<?php

namespace Modules\DataImportExport\App\Imports;

use App\Models\Client;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ClientImport implements ToModel, WithHeadingRow
{
  /**
   * Create a new Client instance for each row.
   *
   * @param array $row
   * @return Client|null
   */
  public function model(array $row)
  {
    return new Client([
      'name' => $row['name'],
      'email' => $row['email'],
      'address' => $row['address'] ?? null,
      'phone' => $row['phone'] ?? null,
      'latitude' => $row['latitude'] ?? null,
      'longitude' => $row['longitude'] ?? null,
      'contact_person_name' => $row['contact_person_name'] ?? null,
      'radius' => $row['radius'] ?? null,
      'city' => $row['city'] ?? null,
      'state' => $row['state'] ?? null,
      'remarks' => $row['remarks'] ?? null,
      'image_url' => $row['image_url'] ?? null,
      'status' => $row['status'] ?? 'active',
      'created_by_id' => auth()->id(),
      'updated_by_id' => auth()->id(),
    ]);
  }
}
