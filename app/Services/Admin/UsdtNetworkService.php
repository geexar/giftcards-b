<?php

namespace App\Services\Admin;

use App\Repositories\UsdtNetworkRepository;
use App\Services\CCPayment\CCPaymentService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UsdtNetworkService
{
    public function __construct(
        private CCPaymentService $ccPaymentService,
        private UsdtNetworkRepository $usdtNetworkRepository
    ) {}

    public function getNetwork(string $id)
    {
        $network = $this->usdtNetworkRepository->getById($id);

        if (!$network) {
            throw new NotFoundHttpException('Network not found');
        }

        return $network;
    }

    public function create(array $data)
    {
        // Get available networks from the service
        $availableNetworks = $this->ccPaymentService->getAvailableNetworks();

        // Extract identifiers from the available networks
        $validIdentifiers = collect($availableNetworks)->pluck('identifier')->all();

        // Check if the provided identifier exists in the list
        if (!in_array($data['identifier'], $validIdentifiers)) {
            throw new BadRequestHttpException('this identifier is not available');
        }

        return $this->usdtNetworkRepository->create($data);
    }

    public function update(string $id, array $data)
    {
        $network = $this->getNetwork($id);

        $this->usdtNetworkRepository->update($network, [
            'name' => $data['name'],
            'fixed_fees' => $data['fixed_fees'],
            'percentage_fees' => $data['percentage_fees'],
            'is_active' => $data['is_active']
        ]);
    }

    public function delete(string $id)
    {
        $network = $this->getNetwork($id);

        $network->delete();
    }
}
