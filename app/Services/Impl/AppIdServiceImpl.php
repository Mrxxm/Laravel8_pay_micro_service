<?php


namespace App\Services\Impl;


use App\Models\AppIdModel;
use App\Services\AppIdService;

class AppIdServiceImpl implements AppIdService
{
    public $model = null;

    public function __construct()
    {
        $this->model = new AppIdModel();
    }

    public function list(array $data): array
    {
        $select = ['id', 'app_name', 'app_key', 'app_expire', 'stitching_symbol', 'description', 'create_time', 'update_time', 'delete_time'];

        $keyword = $data['keyword'] ?? '';
        $conditions = [];
        $conditions[] = ['status', '=', 1];
        if (!empty($keyword)) {
            $conditions[] = ['app_name', 'like', "%{$keyword}%"];
        }

        $result = $this->model->list($select, $conditions);

        if (count($result)) {
            foreach ($result['data'] as &$res) {
                $res['delete_date'] = DateFormat($res['delete_time']);
            }
        }

        return $result;
    }

    public function add(array $fields): void
    {
        $this->model->add($fields);
    }

    public function update(int $id, array $fields): void
    {
        // TODO: Implement update() method.
    }

    public function delete(int $id): void
    {
        // TODO: Implement delete() method.
    }

}
