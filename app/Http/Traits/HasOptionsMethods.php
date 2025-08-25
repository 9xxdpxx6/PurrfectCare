<?php

namespace App\Http\Traits;

use App\Services\Options\SupplierOptionsService;
use App\Services\Options\DrugOptionsService;
use App\Services\Options\EmployeeOptionsService;
use App\Services\Options\ClientOptionsService;
use App\Services\Options\PetOptionsService;
use App\Services\Options\StatusOptionsService;
use App\Services\Options\BranchOptionsService;
use App\Services\Options\ServiceOptionsService;
use App\Services\Options\SpecialtyOptionsService;
use App\Services\Options\ScheduleOptionsService;
use App\Services\Options\SymptomOptionsService;
use App\Services\Options\DiagnosisOptionsService;
use App\Services\Options\LabTestOptionsService;
use App\Services\Options\VaccinationOptionsService;
use App\Services\Options\BreedOptionsService;
use Illuminate\Http\Request;

trait HasOptionsMethods
{
    public function supplierOptions(Request $request)
    {
        return app(SupplierOptionsService::class)->getOptions($request);
    }

    public function drugOptions(Request $request)
    {
        return app(DrugOptionsService::class)->getOptions($request);
    }

    public function veterinarianOptions(Request $request)
    {
        return app(EmployeeOptionsService::class)->getVeterinarianOptions($request);
    }

    public function managerOptions(Request $request)
    {
        return app(EmployeeOptionsService::class)->getManagerOptions($request);
    }

    public function clientOptions(Request $request)
    {
        return app(ClientOptionsService::class)->getOptions($request);
    }

    public function ownerOptions(Request $request)
    {
        return app(ClientOptionsService::class)->getOptions($request);
    }

    public function petOptions(Request $request)
    {
        return app(PetOptionsService::class)->getOptions($request);
    }

    public function statusOptions(Request $request)
    {
        return app(StatusOptionsService::class)->getOptions($request);
    }

    public function branchOptions(Request $request)
    {
        return app(BranchOptionsService::class)->getOptions($request);
    }

    public function serviceOptions(Request $request)
    {
        return app(ServiceOptionsService::class)->getOptions($request);
    }

    public function specialtyOptions(Request $request)
    {
        return app(SpecialtyOptionsService::class)->getOptions($request);
    }

    public function scheduleOptions(Request $request)
    {
        return app(ScheduleOptionsService::class)->getOptions($request);
    }

    public function symptomOptions(Request $request)
    {
        return app(SymptomOptionsService::class)->getOptions($request);
    }

    public function diagnosisOptions(Request $request)
    {
        return app(DiagnosisOptionsService::class)->getOptions($request);
    }

    public function labTestTypeOptions(Request $request)
    {
        return app(LabTestOptionsService::class)->getLabTestTypeOptions($request);
    }

    public function labTestParamOptions(Request $request)
    {
        return app(LabTestOptionsService::class)->getLabTestParamOptions($request);
    }

    public function labTestOptions(Request $request)
    {
        return app(LabTestOptionsService::class)->getLabTestOptions($request);
    }

    public function vaccinationOptions(Request $request)
    {
        return app(VaccinationOptionsService::class)->getOptions($request);
    }

    public function breedOptions(Request $request)
    {
        return app(BreedOptionsService::class)->getOptions($request);
    }
} 