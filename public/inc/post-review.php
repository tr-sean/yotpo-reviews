<?php

    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    $review = $_POST['review']; // Get array of results

    // Gather form data
    $post_fields = array(
        'appkey'              => "USSMkKWzXuEUM56NQBV6qrqgs9bqhA2uDaj9fIbN",
        'domain'              => "http://www.thankgodforcoffee.com",
        'sku'                 => $_POST['product_id'],
        'product_title'       => $_POST['product_title'],
        'product_url'         => $_POST['product_url'],
        'product_image_url'   => $_POST['product_image'],
        'display_name'        => $review['name'],
        'email'               => $review['email'],
        'review_content'      => $review['content'],
        'review_title'        => $review['subject'],
        'review_score'        => $review['rating']
    );
    $post_fields = json_encode($post_fields);

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL            => 'https://api.yotpo.com/v1/widget/reviews',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => '',
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_POSTFIELDS     => $post_fields,
        CURLOPT_HTTPHEADER     => array( 'Content-Type: application/json' ),
    ));

    $response = curl_exec($curl);
    curl_close($curl);
    $response = json_decode( $response, true );

    $result = $response['code'] == 200 ? 'posted' : 'failed';
    header( 'Location: ' . $_POST['product_url'] . '?review=' . $result );
