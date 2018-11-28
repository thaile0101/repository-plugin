<?php 
namespace ThaiLe\Repository\Contracts;

/**
 * Interface CriteriaInterface
 * @package ThaiLe\Repository\Contracts
 */
interface CriteriaInterface
{
    /**
     * Apply criteria in query repository
     *
     * @param                     $model
     * @param RepositoryInterface $repository
     *
     * @return mixed
     */
    public function apply($model, RepositoryInterface $repository);
}
