<?php

namespace App\Filament\Resources\Loans\Pages;

use App\Enums\LoanStatus;
use App\Filament\Resources\Loans\LoanResource;
use App\Policies\LoanPolicy;
use Filament\Resources\Pages\CreateRecord;

class CreateLoan extends CreateRecord
{
    protected static string $resource = LoanResource::class;

    protected static ?string $title = 'Ajukan Peminjaman';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (! LoanPolicy::manages(auth()->user())) {
            $data['user_id'] = auth()->id();
        }

        $data['status'] = LoanStatus::Diajukan;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
