<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    /**
     * Retorna uma lista de todas as empresas.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $companies = Company::select('id', 'name')->get();
        return response()->json($companies);
    }
}
