<?php

namespace Api\Controllers\Api\Animal;

use Api\Core\Factories\ResponseFactory;
use Api\Core\Http\BaseController;
use Api\Core\Models\Account;
use Api\Core\Models\Animal;
use Api\Core\Models\Location;
use Api\Core\Models\Type;
use Api\Core\Services\Authorization;
use Api\Core\Services\Formatters\AnimalFormatter;
use Api\Core\Services\Formatters\DateFormatter;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Symfony\Component\Validator\Constraints as Assert;

class CreateController extends BaseController
{
    protected function process(Request $request, Response $response, array $args = []): Response
    {
        $currentAccount = Authorization::getCurrentAccount();
        if ($currentAccount && (!$currentAccount->isAdmin() && !$currentAccount->isChipper()))
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_FORBIDDEN);

        $json = $request->getBody();
        $data = json_decode($json, true);

        $types = Type::whereIn('id', $data['animalTypes'])->get();
        if ($types->count() !== count($data['animalTypes']))
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_NOT_FOUND,
                'Type not found');

        $location = Location::find($data['chippingLocationId']);
        if (!$location)
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_NOT_FOUND,
                'Chipping location not found');

        $chipper = Account::find($data['chipperId']);
        if (!$chipper)
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_NOT_FOUND,
                'Chipper not found');

        $data['lifeStatus'] = Animal::DEFAULT_LIFE_STATUS;
        $data['chippingDateTime'] = DateFormatter::formatToISO8601('now');
        $animal = new Animal($data);

        if (!$animal->save())
            return ResponseFactory::MakeFromStatusCode(StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR);

        $animal->types()->sync($types);
        return ResponseFactory::MakeJSON(AnimalFormatter::prepareOne($animal))
            ->Custom(StatusCodeInterface::STATUS_CREATED);
    }

    protected function validateRequest(Request $request, array $args = []): array
    {
        $json = $request->getBody();
        $data = json_decode($json, true);
        $errors = $this->validate($data, new Assert\Collection([
            "animalTypes" => [
                new Assert\Type('array'),
                new Assert\Count(['min' => 1]),
                new Assert\All([
                    new Assert\Type('numeric'),
                    new Assert\Positive(),
                ]),
            ],
            "weight" => [new Assert\NotNull(), new Assert\Positive()],
            "length" => [new Assert\NotNull(), new Assert\Positive()],
            "height" => [new Assert\NotNull(), new Assert\Positive()],
            "gender" => [new Assert\NotBlank(), new Assert\Choice(Animal::genderValues())],
            "chipperId" => [new Assert\NotNull(), new Assert\Positive()],
            "chippingLocationId" => [new Assert\NotNull(), new Assert\Positive()],
        ]));
        if ($errors) return $errors;

        return $this->validate($data['animalTypes'], new Assert\Unique());
    }
}