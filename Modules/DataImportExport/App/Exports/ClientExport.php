<?php

namespace Modules\DataImportExport\App\Exports;

use App\Models\Client;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ClientExport implements FromCollection, WithHeadings
{
  /**
   * Export data as a collection.
   *
   * @return Collection
   */
  public function collection()
  {
    return Client::select(
      'id',
      'name',
      'email',
      'address',
      'phone',
      'latitude',
      'longitude',
      'contact_person_name',
      'radius',
      'city',
      'state',
      'remarks',
      'image_url',
      'status',
      'created_at',
      'updated_at'
    )->get();
  }

  /**
   * Define column headings.
   *
   * @return array
   */
  public function headings(): array
  {
    return [
      'ID',
      'Name',
      'Email',
      'Address',
      'Phone',
      'Latitude',
      'Longitude',
      'Contact Person Name',
      'Radius',
      'City',
      'State',
      'Remarks',
      'Image URL',
      'Status',
      'Created At',
      'Updated At',
    ];
  }
}
