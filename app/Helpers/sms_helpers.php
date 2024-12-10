<?php
// bulk.ke sms sending

use App\Models\MobileOutgoing;

 function sendSMS($message, $phone, $outgoing)
    {
        $outgoing = MobileOutgoing::find($outgoing);
        $curl = curl_init();

        // Prepare the data as an associative array
        $data = [
            'SenderId' => $outgoing->shortcode, // Replace with your sender ID
            'IsUnicode' => true,
            'IsFlash' => true,
            // 'ScheduleDateTime' => 'string', // Replace with your date and time in proper format
            'MessageParameters' => [
                [
                    'Number' => $phone, // Replace with the actual number
                    'Text' => $message,       // Replace with the actual message text
                ],
            ],
            'ApiKey' => $outgoing->api_key, // Replace with your API key
            'ClientId' => $outgoing->api_user, // Replace with your client ID

            //bulk ke data object
            'mobile' => $phone,
            'response_type' => 'json',
            'sender_name' => $outgoing->shortcode,
            'service_id' => 0,
            'message' => $message,
        ];

        curl_setopt_array($curl, [
            CURLOPT_URL => $outgoing->api_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),  // Encode the array to JSON
            CURLOPT_HTTPHEADER => [
                "AccessKey: $outgoing->api_key", //used by onfon
                "h_api_key: $outgoing->api_key",
                'Content-Type: application/json',
            ],
        ]);

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            // Log the error message
            echo 'Error:'.curl_error($curl);
            // Log::error('Error:'.curl_error($curl));
        }

        curl_close($curl);
        // Log::info($response);

        // Return the response
        return $response;
    }

function formatPhoneNumber($phoneNumber)
{
    // Remove non-numeric characters
    $cleanedNumber = preg_replace('/\D/', '', $phoneNumber);

    // Ensure the number starts with 254
    if (!str_starts_with($cleanedNumber, '254')) {
        $cleanedNumber = str_starts_with($cleanedNumber, '0')
            ? '254' . substr($cleanedNumber, 1)
            : '254' . $cleanedNumber;
    }

    return $cleanedNumber;
}
