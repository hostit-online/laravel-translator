<?php

namespace HostitOnline\LaravelTranslator\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class TranslatableScope implements Scope
{
    private $translationTable = '';

    public function apply(Builder $builder, Model $model): void
    {
        $this->translationTable = config('laravel-translation.translation_table');
        $translatableColumns = $this->getTranslatableColumns($model);

        if (!$translatableColumns) {
            return;
        }

        $selectedTranslatableColumns = $this->getSelectedTranslatableColumns($builder, $translatableColumns);

        $newSelectStatement = $this->translateSelectedColumns($builder, $selectedTranslatableColumns, $model);
        $originalSelectSql = $this->getOriginalSelect($builder);
        $isSelectAll = $this->isSelectAllQuery($originalSelectSql);

        if ($isSelectAll) {
            $this->addSelectAllToBuilder($builder, $model->getTable(), $selectedTranslatableColumns);
        }

        $builder->addSelect(DB::raw($newSelectStatement));
        $this->updateWhereStatementWithPrefix($builder, $model);
        $this->cleanupQueryColumns($builder, $selectedTranslatableColumns);
        $builder->groupBy($model->getTable() . '.id');
    }

    private function updateWhereStatementWithPrefix(Builder $builder, Model $model): void
    {
        foreach ($builder->getQuery()->wheres as &$whereStatement) {
            if (isset($whereStatement['query']) && $whereStatement['query']->from === $model->getTable()) {
                foreach ($whereStatement['query']->wheres as &$where) {
                    if (!Str::contains($where['column'], $model->getTable())) {
                        $where['column'] = sprintf('%s.%s', $model->getTable(), $where['column']);
                    }
                }
            }
        }
    }

    /**
     * Check if the original query was a select * query.
     * If so, we use that to add the select statements of all columns
     */
    private function isSelectAllQuery(string $selectSql): bool
    {
        return Str::contains($selectSql, '*');
    }

    /**
     * Select all non-translatable columns from the table.
     */
    private function addSelectAllToBuilder(Builder $builder, string $table, array $translatedColumns): void
    {
        foreach (Schema::getColumnListing($table) as $column) {
            if (!in_array($column, $translatedColumns, true)) {
                $builder->addSelect(sprintf('%s.%s', $table, $column));
            }
        }
    }

    /**
     * Return the SQL between SELECT .... FROM
     * Which we can use to check later.
     */
    private function getOriginalSelect(Builder $builder): string
    {
        return Str::between($builder->getQuery()->toSql(), 'select ', ' from');
    }

    /**
     * This needs to be done due to how laravels query builder works.
     * We remove any translatable columns from the original SQL statement
     * as they are replaced with a custom SQL statement that will return a translation.
     */
    private function cleanupQueryColumns(Builder $builder, array $selectedTranslatableColumns): void
    {
        foreach ($builder->getQuery()->getColumns() as $key => $column) {
            if (in_array($column, $selectedTranslatableColumns, true)) {
                unset($builder->getQuery()->columns[$key]);
            }
        }
    }

    /**
     * Add new select statements to the query which will do select ... as ... to automatically return the translated
     * values inside the model.
     */
    private function translateSelectedColumns(
        Builder $builder,
        array $selectedTranslatableColumns,
        Model $model
    ): string {
        $builder->leftJoin($this->translationTable, function ($join) use ($model, $selectedTranslatableColumns) {
            $join->on(sprintf('%s.translatable_id', $this->translationTable), '=', "{$model->getTable()}.id")
                ->where(sprintf('%s.translatable_type', $this->translationTable), '=', get_class($model))
                ->where(sprintf('%s.iso_code', $this->translationTable), '=', App::getLocale())
                ->whereIn(sprintf('%s.translatable_column', $this->translationTable), $selectedTranslatableColumns);
        });

        $selectStatements = [];
        foreach ($selectedTranslatableColumns as $selectedColumn) {
            $selectStatements[] = sprintf(
                "COALESCE(
                            MAX(
                                CASE
                                WHEN %s.translatable_column = '%s' THEN
                                 translations.value END
                            ), %s.%s) AS %s",
                $this->translationTable,
                $selectedColumn,
                $model->getTable(),
                $selectedColumn,
                $selectedColumn
            );
        }

        return implode(',', $selectStatements);
    }

    /**
     * Get array (or null) from the model translatable list.
     * This list will contain all columns which (should) be translated.
     * We do this, so we don't have to check for each column in the table.
     */
    private function getTranslatableColumns(Model $model): ?array
    {
        return $model->translatable;
    }

    /**
     * Return an array of columns that are actually selected and are in the $translatableColumns
     * If everything is selected, we can return the array directly.
     */
    private function getSelectedTranslatableColumns(Builder $builder, array $translatableColumns): array
    {
        $query = $builder->getQuery();
        $sql = $query->toSql();

        $matches = explode(',', str_replace('`', '', Str::between($sql, 'select ', ' from')));

        if ($matches[0] === '*') {
            return $translatableColumns;
        }

        $matchedColumns = [];

        foreach ($matches as $match) {
            if (in_array($match, $translatableColumns)) {
                $matchedColumns[] = $match;
            }
        }

        return $matchedColumns;
    }
}
