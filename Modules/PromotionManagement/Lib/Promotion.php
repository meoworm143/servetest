<?php

if (!function_exists('device_notification')) {
    function device_notification($fcm_token, $title, $description, $image, $booking_id): bool|string
    {
        $config = business_config('push_notification', 'third_party');
        $url = "https://fcm.googleapis.com/fcm/send";
        $header = array("authorization: key=" . $config->live_values['server_key'],
            "content-type: application/json"
        );

        $image = asset('storage/app/public/push-notification') . '/' . $image;

        $postdata = '{
            "to" : "' . $fcm_token . '",
            "notification" : {
                    "title":"' . $title . '",
                    "body" : "' . $description . '",
                    "booking_id": "' . $booking_id . '",
                    "type": "status",
                    "title_loc_key": "' . $booking_id . '",
                    "body_loc_key": "status",
                    "image": "' . $image . '",
                    "sound": "notification.wav",
                    "android_channel_id": "ondemand"
                },
                "data": {
                    "title":"' . $title . '",
                    "body" : "' . $description . '",
                    "booking_id": "' . $booking_id . '",
                    "type": "status",
                    "image": "' . $image . '",
                }
             }';

        return send_curl_request($url, $postdata, $header);
    }
}

if (!function_exists('topic_notification')) {
    function topic_notification($topic, $title, $description, $image, $booking_id): bool|string
    {
        $config = business_config('push_notification', 'third_party');

        $url = "https://fcm.googleapis.com/fcm/send";
        $header = ["authorization: key=" . $config->live_values['server_key'],
            "content-type: application/json",
        ];

        $image = asset('storage/app/public/push-notification') . '/' . $image;
        $topic_str = "/topics/" . $topic;

        $postdata = '{
             "to":"' . $topic_str . '",
             "notification" : {
                    "title":"' . $title . '",
                    "body" : "' . $description . '",
                    "booking_id": "' . $booking_id . '",
                    "type": "status",
                    "title_loc_key": "' . $booking_id . '",
                    "body_loc_key": "status",
                    "image": "' . $image . '",
                    "sound": "notification.wav",
                    "android_channel_id": "ondemand"
                },
                "data": {
                    "title":"' . $title . '",
                    "body" : "' . $description . '",
                    "booking_id": "' . $booking_id . '",
                    "type": "status",
                    "image": "' . $image . '",
                }
              }';

        return send_curl_request($url, $postdata, $header);
    }
}

if (!function_exists('basic_discount_calculation')) {
    function basic_discount_calculation($service, $total_purchase_amount): float
    {
        $keeper = null;
        if ($service->service_discount->count() > 0) {
            $keeper = $service->service_discount[0]->discount;
        } elseif ($service->category->category_discount->count() > 0) {
            $keeper = $service->category->category_discount[0]->discount;
        }

        return booking_discount_calculator($keeper, $total_purchase_amount);
    }
}

if (!function_exists('campaign_discount_calculation')) {
    function campaign_discount_calculation($service, $total_purchase_amount): float
    {
        $keeper = null;
        if ($service->campaign_discount->count() > 0) {
            $keeper = $service->campaign_discount[0]->discount;
        }elseif($service->category->campaign_discount->count() > 0){
            $keeper = $service->category->campaign_discount[0]->discount;
        }

        return booking_discount_calculator($keeper, $total_purchase_amount);
    }
}

/**
 * @param string $url
 * @param string $postdata
 * @param array $header
 * @return bool|string
 */
function send_curl_request(string $url, string $postdata, array $header): string|bool
{
    $ch = curl_init();
    $timeout = 120;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

    // Get URL content
    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}

/**
 * @param mixed $keeper
 * @param $total_purchase_amount
 * @return mixed
 */
function booking_discount_calculator(mixed $keeper, $total_purchase_amount): float
{
    $amount = 0;

    if ($keeper != null && $total_purchase_amount >= $keeper->min_purchase) {
        if ($keeper->discount_amount_type == 'percent') {
            $amount = ($total_purchase_amount / 100) * $keeper->discount_amount;

            if ($amount > $keeper->max_discount_amount) {
                $amount = $keeper->max_discount_amount;
            }

        } else {
            $amount = $keeper->discount_amount;
        }
    }

    return $amount;
}
