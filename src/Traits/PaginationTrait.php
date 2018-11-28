<?php 
namespace ThaiLe\Repository\Traits;

use Illuminate\Support\Facades\DB;

trait PaginationTrait
{
    private $page = 1;
    private $limit;

    /**
     * Extract paging, sort, limit data from request input
     *
     * @param array $params
     *
     * @return array
     */
    public function extractPagination($params = [])
    {
        $allowedSortOrder = ['desc' => '-'];
        $pagination = [
            'count'      => config('api.default_limit'),
            'sortColumn' => 'id',
            'sortOrder'  => 'desc',
            'skip'       => 0,
            'page'  => 1,
            'offset'    => 0,
            'count_only'    => 0,
        ];

        if (!empty($params['page'])) {
            $pagination['page'] = intval($params['page']);
        }

        if (!empty($params['count_only'])) {
            $pagination['count'] = 1;
        }
        else if ( isset($params['count']) && intval($params['count']) > 0 ) {
            $pagination['count'] = intval($params['count']);
        }

        if ( isset($params['skip']) && intval($params['skip']) > 0 ) {
            $pagination['skip'] = intval($params['skip']);
        }

        if ( !empty($params['sort']) ) {
            $sortOrder                = trim(isset($params['sort']) && is_string($params['sort']) ? substr($params['sort'], 0, 1) : '');
            $pagination['sortOrder']  = in_array($sortOrder, $allowedSortOrder) ? 'desc' : 'asc';
            $pagination['sortColumn'] = in_array($sortOrder, $allowedSortOrder) ? substr($params['sort'], 1, strlen($params['sort']) - 1) : $params['sort'];
        }

        $pagination['offset'] = ($pagination['page'] - 1) * $pagination['count'];

        $this->setPage($pagination['page']);
        $this->setLimit($pagination['count']);

        return $pagination;
    }

    /**
     * @param array $columns
     * @return string
     */
    public function selectWithFoundRows($columns = []) {
        $selectClause = implode(',', $columns);
        return ' SQL_CALC_FOUND_ROWS '. $selectClause;
    }

    /**
     * Get total rows of the previous query
     * @return integer
    */
    private function getTotal() {
        return (int) DB::select("SELECT FOUND_ROWS() as `row_count`")[0]->row_count;
    }

    /**
     * @param $result
     * @return array $result
     */
    public function result($result) {
        $result->total = $this->getTotal();
        $result->limit = $this->getLimit();
        $result->page = $this->getPage();

        $result->lastPage = ($this->getTotal() - $this->getTotal() % $this->getLimit())/$this->getLimit() + 1;

        return $result;
    }

    /**
     * @return int
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param int $page
     */
    public function setPage($page)
    {
        $this->page = $page;
    }

    /**
     * @return mixed
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param mixed $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }
}