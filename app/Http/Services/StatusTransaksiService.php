<?php

namespace App\Http\Services;

use Illuminate\Database\Eloquent\Model;

class StatusTransaksiService
{
    public function update(Model $model): array
    {
        if ($this->isTransactionCompleted($model)) {
            throw new \Exception('Pesanan sudah selesai!');
        }

        switch ($model['status']) {
            case 'proses':
                $model->update(['status' => 'siap_diambil']);
                return ['data' => $model];

            case 'siap_diambil':
                $model->update(['status' => 'selesai']);
                return ['data' => $model];

            default:
                throw new \InvalidArgumentException('Invalid status');
        }
    }

    public function confirmInitialTransaction(array $data, Model $model): array
    {
        if ($this->isTransactionCompleted($model)) {
            throw new \Exception('Pesanan sudah selesai!');
        }

        $model->update($data);
        return ['data' => $model];
    }

    protected function isTransactionCompleted(Model $model): bool
    {
        return in_array($model['status'], ['dibatalkan', 'selesai']);
    }
}