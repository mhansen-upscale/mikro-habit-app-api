<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class DataService
{
    public static ?DataService $instance = NULL;
    private string $sort;
    private mixed $perPage;
    private mixed $filter;
    private mixed $relations;
    private mixed $model;
    private mixed $result;
    private mixed $sql;
    private mixed $joins;

    private mixed $counts;

    private mixed $groupBy;

    private array $sums;

    private mixed $extraFilter;

    private string|null $select;

    private function __construct($model, $queryData, $extraFilter = null)
    {
        $this->setModel($model);
        $this->setSort($queryData["sort"] ?? "id|ASC");
        $this->setSelect($queryData["select"] ?? null);
        $this->setPerPage($queryData["perPage"] ?? 1000);
        $this->setFilter($queryData["filter"] ?? []);
        $this->setRelations($queryData["relations"] ?? []);
        $this->setJoins($queryData["joins"] ?? []);
        $this->setCounts($queryData["counts"] ?? []);
        $this->setGroupBy($queryData["groupBy"] ?? null);
        $this->setSums($queryData["aggregate"] ?? []);
        $this->setExtraFilter($extraFilter ?? []);

        $this->query();
    }

    /**
     * @param $model
     * @param array $queryData
     */
    public static function getInstance($model, array $queryData, $extraFilter = null): ?DataService
    {
        if(self::$instance == NULL) {
            self::$instance = new DataService($model, $queryData, $extraFilter);
        }

        return self::$instance;
    }

    /**
     * @param $model
     * @return void
     */
    private function setModel($model): void
    {
        $this->model = $model;
    }

    /**
     * @return string
     */
    public function getModel(): string
    {
        return $this->select;
    }

    /**
     * @param $select
     * @return void
     */
    private function setSelect($select): void
    {
        $this->select = $select;
    }

    /**
     * @return string|null
     */
    public function getSelect(): string|null
    {
        return $this->select;
    }

    /**
     * @param $sort
     * @return void
     */
    private function setSort($sort): void
    {
        $this->sort = $sort;
    }

    /**
     * @return string
     */
    public function getSort(): string
    {
        return $this->sort;
    }

    /**
     * @param $groupBy
     * @return void
     */
    private function setGroupBy($groupBy): void
    {
        $this->groupBy = $groupBy;
    }

    /**
     * @return string|null
     */
    public function getGroupBy(): string|null
    {
        return $this->groupBy;
    }

    /**
     * @param $sums
     * @return void
     */
    private function setSums($sums): void
    {
        if(is_string($sums)) {
            $sums = json_decode($sums, TRUE);
        }
        $this->sums = $sums;
    }

    /**
     * @return array
     */
    public function getSums(): array
    {
        return $this->sums;
    }

    /**
     * @param $perPage
     * @return void
     */
    private function setPerPage($perPage): void
    {
        $this->perPage = $perPage;
    }

    /**
     * @return string
     */
    public function getPerPage(): mixed
    {
        return $this->perPage;
    }

    /**
     * @param $filter
     * @return void
     */
    private function setFilter($filter): void
    {
        if(is_string($filter)) {
            $filter = json_decode($filter, TRUE);
        }
        $this->filter = $filter;
    }

    /**
     * @return string
     */
    public function getFilter(): string
    {
        return $this->filter;
    }

    /**
     * @param $extraFilter
     * @return void
     */
    private function setExtraFilter($extraFilter): void
    {
        if(is_string($extraFilter)) {
            $extraFilter = json_decode($extraFilter, TRUE);
        }
        $this->extraFilter = $extraFilter;
    }

    /**
     * @return string
     */
    public function getExtraFilter(): string
    {
        return $this->extraFilter;
    }

    /**
     * @param $relations
     * @return void
     */
    private function setRelations($relations): void
    {
        if(is_string($relations)) {
            $relations = json_decode($relations, TRUE);
        }
        $this->relations = $relations;
    }

    /**
     * @return mixed
     */
    public function getRelations(): mixed
    {
        return $this->relations;
    }

    private function setJoins($joins): void
    {
        if(is_string($joins)) {
            $joins = json_decode($joins, TRUE);
        }
        $this->joins = $joins;
    }

    /**
     * @return array
     */
    public function getJoins(): mixed
    {
        return $this->joins;
    }

    private function setCounts($counts): void
    {
        if(is_string($counts)) {
            $counts = json_decode($counts, TRUE);
        }
        $this->counts = $counts;
    }

    /**
     * @return array
     */
    public function getCounts(): mixed
    {
        return $this->counts;
    }

    /**
     * @return void
     */
    private function query()
    {
        $model = new ($this->model);

        if(!empty($this->getSelect())) {
            $selects = explode(",", $this->getSelect());
        } else {
            $selects[] = $model->getTable() . ".*";
        }

        if(!empty($this->getSums())) {
            foreach($this->getSums() as $aggregate) {
                $a = $aggregate;
                if(is_string($aggregate)) {
                    $a = json_decode($aggregate, TRUE);
                }
                $selects[] = "{$a['type']}({$a['table']}.{$a['column']}) as {$a['alias']}";
            }
        }

        $query = ($this->model)::select(DB::raw(implode(",", $selects)));

        if(!empty($this->getRelations())) {
            $query->with($this->getRelations());
        }

        if(!empty($this->getCounts())) {
            $query->withCount($this->getCounts());
        }

        if(!empty($this->joins)) {
            foreach($this->joins as $j) {
                $query->join($j["table"], $j["foreign_column"], $j["operator"], $j["self_column"]);
            }
        }

        if(!empty($this->getSort())) {
            $sortBy = explode("|", $this->getSort());
            $query->orderby($sortBy[0], $sortBy[1]);
        } else {
            $query->orderby("ASC", $model->getTable() . ".id");
        }

        if(!empty($this->extraFilter)) {
            $this->filter[] = $this->extraFilter;
        }

        foreach($this->filter as $filter) {

            $f = $filter;
            if(is_string($filter)) {
                $f = json_decode($filter, TRUE);
            }

            if(!empty($f)) {
                if(isset($f["container"])) {
                    $query->{$f["container"]['type']}(function($query) use ($f) {
                        foreach($f["container"]['queries'] as $q) {
                            if($q["type"] == "whereRelation") {
                                $query->{$q['type']}($q["relation"], $q["key"], $q["operator"], $q["criteria"]);
                            } else if($q["type"] == "whereIn") {
                                $query->{$q['type']}($q["key"], $q["criteria"]);
                            } else {
                                $query->{$q['type']}($q["key"], $q["operator"], $q["criteria"]);
                            }
                        }
                    });
                } else {
                    if($f["type"] == "whereRelation") {
                        $query->{$f['type']}($f["relation"], $f["key"], $f["operator"], $f["criteria"]);
                    } else {
                        $query->{$f['type']}($f["key"], $f["operator"], $f["criteria"]);
                    }
                }
            }
        }

        $this->sql = $query->toSql();

        if(!empty($this->getGroupBy())) {
            $query->groupby($this->getGroupBy());
        }


        if(!empty($this->getPerPage())) {
            $this->result = $query->paginate($this->getPerPage());
        } else {
            $this->result = $query->get();
        }
    }

    /**
     * @return mixed
     */
    public function getResult() {
        return $this->result;
    }

    public function getSql() {
        return $this->sql;
    }

}
