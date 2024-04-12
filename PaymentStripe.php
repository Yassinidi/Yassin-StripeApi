namespace App\Traits;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use Exception;

class PaymentStipe
{
    public $CARD_TOKEN="";
    public $CURRENCY="";
    public function getToken($cardNumber, $cardExpMonth, $cardExpYear, $cardCvc)
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => 'Bearer ' . env('STRIPE_PUBLISHABLE_KEY')
            ])->post('https://api.stripe.com/v1/tokens', [
                'card' => [
                    'number' => $cardNumber,
                    'exp_month' => $cardExpMonth,
                    'exp_year' => $cardExpYear,
                    'cvc' => $cardCvc
                ]
            ]);

            $response->throw();

            return $response->json();
        } catch (RequestException $e) {
            \Log::error('Error getting token: ' . $e->getMessage());
            throw $e;
        }
    }
    public static function  isValidCard($cardNumber, $cardExpMonth, $cardExpYear, $cardCvc)
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => 'Bearer ' . env('STRIPE_PUBLISHABLE_KEY')
            ])->post('https://api.stripe.com/v1/tokens', [
                'card' => [
                    'number' => $cardNumber,
                    'exp_month' => $cardExpMonth,
                    'exp_year' => $cardExpYear,
                    'cvc' => $cardCvc
                ]
            ]);

            $response->throw();

            return $response->json();
        } catch (RequestException $e) {
            \Log::error('Error getting token: ' . $e->getMessage());
            throw $e;
        }
    }
    public function sendPayment($price,$description)
    {
        try {
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => 'Bearer ' . env('STRIPE_SECRET_KEY')
            ])->post('https://api.stripe.com/v1/charges', [
                'amount' => $price,
                'currency' => $this->CURRENCY,
                'source' => $this->CARD_TOKEN,
                'description' => $description
            ]);

            $response->throw();

            return $response->json();
        } catch (RequestException $e) {
            \Log::error('Error sending payment: ' . $e->getMessage());
            throw $e;
        }
    }
    public function subscribeUser($creditCardToken) {
  
    $this->CARD_TOKEN = $creditCardToken['id'];

    return 'Subscription successful';
}


    public static function payWithThisCard($cardNumber, $cardExpMonth, $cardExpYear, $cardCvc, $price,$currency,$description)
    {
        try {
            $this->CURRENCY=$currency,
            $creditCardToken = $this->getToken($cardNumber, $cardExpMonth, $cardExpYear, $cardCvc);
            $this->subscribeUser($creditCardToken);

            $paymentData = $this->sendPayment($price,$description);

            if ($paymentData['status'] === 'succeeded') {
                return $paymentData;
            } else {
                throw new Exception('Payment failed');
            }
        } catch (Exception $e) {
            \Log::error('Error during payment: ' . $e->getMessage());
            throw $e;
        }
    }
}
