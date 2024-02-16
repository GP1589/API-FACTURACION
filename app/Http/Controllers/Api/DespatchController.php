<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\SunatService;
use App\Traits\SunatTrait;
use Greenter\Report\XmlUtils;
use Illuminate\Http\Request;

class DespatchController extends Controller
{
    use SunatTrait;
    public function send(Request $request)
    {
        // $request->validate([
        //     'company' => 'required|array',
        //     'company.address' => 'required|array',
        //     'client' => 'required|array',
        //     'details' => 'required|array',
        //     'details.*' => 'required|array',
        // ]);
        $data = $request->all();

        $company = Company::where('user_id', auth()->id())
                    ->where('ruc', $data['company']['ruc'])
                    ->firstOrFail();

        //return $data;
        $sunat = new SunatService();
        // dd($data);
        $despatch=$sunat->getDespatch($data);
        $api=$sunat->getSeeApi($company);
        $result=$api->send($despatch);

        $ticket =$result->getTicket();
        $result=$api->getStatus($ticket);

        $response['xml'] = $api->getLastXml();
        $response['hash'] = (new XmlUtils())->getHashSign($response['xml']);
        $response['sunatResponse'] = $sunat->sunatResponse($result);

        return $response;
    }
    
    public function xml(Request $request)
    {
        $data = $request->all();

        $company = Company::where('user_id', auth()->id())
                    ->where('ruc', $data['company']['ruc'])
                    ->firstOrFail();

        $sunat = new SunatService;
        $see = $sunat->getSee($company);
        $despatch = $sunat->getDespatch($data);   

        $response['xml'] = $see->getXmlSigned($despatch);
        $response['hash'] = (new XmlUtils())->getHashSign($response['xml']);

        return $response;
        //return response()-json($response,200);
    }

    public function pdf(Request $request){

        $data = $request->all();

        $company = Company::where('user_id', auth()->id())
                    ->where('ruc', $data['company']['ruc'])
                    ->firstOrFail();

        $sunat = new SunatService;
        $despatch = $sunat->getDespatch($data);

        //$pdf = $sunat->getHtmlReport($note);
        return $sunat->getHtmlReport($despatch);
        //$pdf = $sunat->generatePdfReport($note);
    }
}
