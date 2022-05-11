<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BeersController extends AbstractApiController
{
    private $client;

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }
    
    /**
     * Obtiene todas las cervezas y puede ser filtrado con el alimento
     *
     * @param Request $request {food:string}
     * @return Response
     */
    public function index(Request $request) : Response
    {
        // Variables
        $maxSearachPerPage = 80;
        $paramsQuery = ["per_page" => $maxSearachPerPage, "page" => 1];
        $requestSearchFood = $request->get('food', "");

        // Validaciones
        if(!empty($requestSearchFood))
            $paramsQuery["food"] = str_replace(" ", "_", $requestSearchFood);

        
        // Limite por consulta son 80, si hay más vuelve
        // y genera el consumo con la siguiente página
        $beers = [];
        do{
            $response = $this->client->request('GET', 'https://api.punkapi.com/v2/beers', [
                "query" => $paramsQuery
            ]);
            $contentBeers = $response->toArray();
            foreach($contentBeers as $beer){
                $beers[] = [
                    "id" => $beer["id"],
                    "name" => $beer["name"],
                    "description" => $beer["description"]
                ];
            }

            // Adicionamos la página
            $paramsQuery["page"]++;
        }while(count($contentBeers) >= $maxSearachPerPage);

        return $this->respond($beers);
    }

    /**
     * Obtiene información de la cerveza por medio de un id
     *
     * @param Request $request {id:number}
     * @return Response
     */
    public function show(Request $request) : Response{
        // Variables
        $beerId = $request->get('id');

        // API
        $response = $this->client->request('GET', "https://api.punkapi.com/v2/beers/{$beerId}");
        $statusCode = $response->getStatusCode();
        if($statusCode !== Response::HTTP_OK){
            throw new NotFoundHttpException('No results found');
        }

        // Result
        $contentBeers = $response->toArray();
        $beer = $contentBeers[0];
        $data = [
            "image_url" => $beer["image_url"],
            "tagline" => $beer["tagline"],
            "first_brewed" => $beer["first_brewed"]
        ];
        return $this->respond($data);
    }
}