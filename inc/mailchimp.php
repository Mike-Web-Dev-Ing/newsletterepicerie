<?php
// inc/mailchimp.php — configuration Mailchimp (à compléter)

// Renseignez votre clé API (format: xxxxxxxxxxxxxxxxxxxxxxxx-us21)
const MAILCHIMP_API_KEY = 'CHANGE_ME-xxxx-us21';

// Préfixe serveur (la partie après le tiret dans la clé API, ex: us21)
const MAILCHIMP_SERVER_PREFIX = 'us21';

// ID de l'audience (List ID)
const MAILCHIMP_LIST_ID = 'CHANGE_ME_LIST_ID';

// Réglages d'expéditeur
const MAILCHIMP_FROM_NAME = 'Franprix Carry-le-Rouet';
const MAILCHIMP_REPLY_TO  = 'magasin1904@franprix.fr';

// Mode d'inscription par défaut pour les nouveaux contacts: 'pending' (double opt-in) ou 'subscribed' (opt-in direct)
const MAILCHIMP_SUBSCRIBE_MODE = 'pending';

function mailchimp_request(string $method, string $path, array $payload = null){
    $url = 'https://' . MAILCHIMP_SERVER_PREFIX . '.api.mailchimp.com/3.0' . $path;
    $ch = curl_init($url);
    $headers = [
        'Content-Type: application/json',
        'Authorization: Basic ' . base64_encode('user:' . MAILCHIMP_API_KEY),
    ];
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => strtoupper($method),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 20,
    ]);
    if ($payload !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    }
    $resp = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);
    return [
        'status' => $status,
        'error' => $err ?: null,
        'body' => $resp ? json_decode($resp, true) : null,
        'raw' => $resp,
    ];
}

// Ajout/mise à jour d'un membre dans la liste (upsert)
function mailchimp_upsert_member(string $email, ?string $firstName = null): array {
    if (MAILCHIMP_API_KEY === 'CHANGE_ME-xxxx-us21' || MAILCHIMP_LIST_ID === 'CHANGE_ME_LIST_ID') {
        return ['status' => 'skipped', 'reason' => 'not_configured'];
    }
    $subscriberHash = md5(strtolower(trim($email)));
    $payload = [
        'email_address' => $email,
        'status_if_new' => (MAILCHIMP_SUBSCRIBE_MODE === 'subscribed') ? 'subscribed' : 'pending',
        'merge_fields'  => [ 'FNAME' => (string)$firstName ],
    ];
    $res = mailchimp_request('PUT', '/lists/' . urlencode(MAILCHIMP_LIST_ID) . '/members/' . $subscriberHash, $payload);
    if ($res['status'] >= 200 && $res['status'] < 300) {
        return ['status' => 'ok', 'response' => $res['body']];
    }
    return ['status' => 'error', 'response' => $res['raw'] ?? null];
}
