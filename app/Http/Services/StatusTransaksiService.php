<?php

namespace App\Http\Services;

use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpFoundation\Response;

class StatusTransaksiService
{
    public function update(Model $model)
    {
        if (in_array($model['status'], ['dibatalkan', 'selesai'])) {
            return [
                'message' => 'Pesanan sudah selesai!',
                'statusCode' => Response::HTTP_UNAUTHORIZED
            ];
        }

        switch ($model['status']) {
            case 'proses':
                $model->update(['status' => 'siap_diambil']);
                return [
                    'message' => ['data' => $model],
                    'statusCode' => Response::HTTP_OK
                ];

            case 'siap_diambil':
                $model->update(['status' => 'selesai']);
                return [
                    'message' => ['data' => $model],
                    'statusCode' => Response::HTTP_OK
                ];
            default:
                return [
                    'message' => 'Invalid status',
                    'statusCode' => Response::HTTP_UNAUTHORIZED
                ];
        }
    }

    public function confirmInitialTransaction(array $data, Model $model)
    {
        $model->update($data);
        return [
            'message' => ['data' => $model],
            'statusCode' => Response::HTTP_OK
        ];
    }
}