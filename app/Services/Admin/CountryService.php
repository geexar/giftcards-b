<?php

namespace App\Services\Admin;

use App\Repositories\CountryRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CountryService
{
    public function __construct(private CountryRepository $countryRepository) {}

    /**
     * Get a country by ID.
     *
     * @param string $id
     * @return \App\Models\Country
     *
     * @throws NotFoundHttpException
     */
    public function getCountry(string $id)
    {
        $country = $this->countryRepository->getById($id);

        if (!$country) {
            throw new NotFoundHttpException('Country not found');
        }

        return $country;
    }

    /**
     * Update a country.
     *
     * @param string $id
     * @param array{is_active?: bool, name: array} $data  // name is translated array
     * @return void
     *
     * @throws NotFoundHttpException
     */
    public function update(string $id, array $data): void
    {
        $country = $this->getCountry($id);

        $this->countryRepository->update($country, [
            'name' => $data['name'],
            'is_active' => $data['is_active']
        ]);
    }

    public function toggleStatus(string $id)
    {
        $country = $this->getCountry($id);

        $country->update(['is_active' => !$country->is_active]);
    }
}
