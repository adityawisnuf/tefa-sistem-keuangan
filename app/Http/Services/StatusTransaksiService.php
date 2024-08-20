<?php

namespace App\Http\Services;

use Illuminate\Database\Eloquent\Model;

class StatusTransaksiService
{
    public function update(Model $model)
    {
        if ($this->isTransactionCompleted($model)) {
            throw new \Exception('Pesanan sudah selesai!');
        }

        switch ($model['status']) {
            case 'proses':
                $model->update(['status' => 'siap_diambil']);
                break;
            case 'siap_diambil':
                $model->update(['status' => 'selesai']);
                break;
            default:
                throw new \Exception('Invalid status');
        }
    }

    public function confirmInitialTransaction(array $data, Model $model)
    {
        if ($this->isTransactionCompleted($model)) {
            throw new \Exception('Pesanan sudah selesai!');
        }

        $model->update($data);
    }

    protected function isTransactionCompleted(Model $model): bool
    {
        return in_array($model['status'], ['dibatalkan', 'selesai']);
    }
}