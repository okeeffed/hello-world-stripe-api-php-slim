<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();

$stripeKey = getenv('SK_TEST_KEY');
\Stripe\Stripe::setApiKey($stripeKey);

$app = AppFactory::create();

// Parse json, form data and xml
$app->addBodyParsingMiddleware();

$app->addRoutingMiddleware();

$app->addErrorMiddleware(true, true, true);

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello world!");
    return $response;
});

$app->post('/api/charge', function (Request $request, Response $response, $args) {
  try {
    $data = $request->getParsedBody();

    // parse attributes from JSON
    $receiptEmail = $data['receiptEmail'];
    $amount = $data['amount'];

    // create the charge
    $charge = \Stripe\Charge::create([
      'amount' => $amount,
      'currency' => 'usd',
      'source' => 'tok_visa',
      'receipt_email' => $receiptEmail
    ]);

    $response->getBody()->write('Successful charge');
    $response->withStatus(201);
    return $response;
  } catch (Exception $e) {
    $response->getBody()->write('Failed charge');
    $response->withStatus(500);
    return $response;
  }
});


$app->run();
