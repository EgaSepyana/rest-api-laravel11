<?php
namespace App\Http\Controllers\API;
use App\Models\ProvinceDimention;
use Illuminate\Http\Request;
use App\Http\Controllers\API\BaseController as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use Validator;
use App\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;

class ProvinceController extends BaseController{
    public function Mvt(Request $request , string $x , string $y , string $z){
        $baseQuery = sprintf("
        with composite as (
                select st_asmvtgeom(st_transform(st_simplify(o.geometry , .01) , 3857) , ST_TileEnvelope(%s, %s, %s) , extent => 4096, buffer => 64) as geom , name , code , postal_codes , longitude , latitude from province_dimention o
            ) select ST_AsMVT(composite.*, 'composite') mvt from composite" , $z , $x , $y);

        error_log($baseQuery);

        $datas = DB::select($baseQuery);
        return response()->stream(function () use($datas){
            fpassthru($datas[0]->mvt);
        } , 200 , [
            "Content-Type" => "application/x-protobuf",
        ]);
    }

    /**
     * @OA\Get(
     *     path="/greet",
     *     tags={"greeting"},
     *     summary="Returns a Sample API response",
     *     description="A sample greeting to test out the API",
     *     operationId="greet",
     *     @OA\Parameter(
     *          name="firstname",
     *          description="nama depan",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *     ),
     *     @OA\Parameter(
     *          name="lastname",
     *          description="nama belakang",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string"
     *          )
     *     ),
     *     @OA\Response(
     *         response="default",
     *         description="successful operation"
     *     )
     * )
     */
    public function GetSussestionProvince(Request $request , string $location_level) {
        $baseQuery = "SELECT code as {{location_level}}_code , name as {{location_level}}_name , postal_codes , json_build_object('langitude' , latitude , 'latitude' , latitude) as coordinates FROM {{location_level}}_dimention where 1 = 1";

        foreach ($request->filter as $filter){
            $baseQuery =  str_replace("where 1 = 1" , sprintf("where 1 = 1 and %s = '%s'" , $filter['field'], $filter['value']) , $baseQuery);
        }

        $baseQuery = str_replace("{{location_level}}" , $location_level , $baseQuery);

        error_log($baseQuery);

        $datas = DB::select($baseQuery);
        
        $datum = [];

        foreach ($datas as $data) {
            $code = $location_level."_code";
            $name = $location_level."_name";

            $object = [
                $code => $data->{$code},
                $name => $data->{$name},
                "postal_codes" => explode("," , trim(trim($data->postal_codes , "{") , "}")),
                "coordinates" => json_decode($data->coordinates, true)
            ];

            array_push($datum , $object);
        }
        return $datum;
    }
}
