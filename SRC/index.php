<?php
// Variabili per salvare la domanda, la risposta e il reasoning
$domanda  = "";
$risposta = "";
$reasoning = "";
$json     = [];

// Questo blocco viene eseguito solo quando l'utente clicca "Invia"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Prendo il testo scritto dall'utente nel campo di testo
    $domanda = $_POST['domanda'];

    // Leggo la API Key dal file .env (passata come variabile d'ambiente da Docker)
    $apiKey = getenv('GROQ_API_KEY');

    // Preparo il "corpo" della richiesta da mandare a Groq
    $dati = [
        "model"    => "openai/gpt-oss-120b",
        "messages" => [
            [
                "role"    => "system",
                "content" => "Sei un assistente utile e conciso che risponde in italiano."
            ],
            [
                "role"    => "user",
                "content" => $domanda
            ]
        ],
        "temperature"       => 0.7,
        "include_reasoning" => true
    ];

    // Uso cURL per fare la richiesta HTTP all'API di Groq
    $ch = curl_init("https://api.groq.com/openai/v1/chat/completions");

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dati));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer " . $apiKey
    ]);

    // Eseguo la richiesta e salvo la risposta
    $risultato = curl_exec($ch);
    curl_close($ch);

    // Converto la risposta JSON in un array PHP
    $json = json_decode($risultato, true);

    // Estraggo il testo della risposta e il reasoning (se presente)
    $risposta  = $json["choices"][0]["message"]["content"];
    $reasoning = $json["choices"][0]["message"]["reasoning"] ?? "";
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Groq Chat</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

    <div class="contenitore">

        <h1>Groq Chat</h1>
        <p class="sottotitolo">Fai una domanda al modello AI</p>

        <!-- Form: invia i dati alla stessa pagina con metodo POST -->
        <form method="POST" action="">
            <label for="domanda">La tua domanda:</label>
            <textarea id="domanda" name="domanda" rows="3" placeholder="Scrivi qui..."><?= htmlspecialchars($domanda) ?></textarea>
            <button type="submit">Invia</button>
        </form>

        <!-- Mostro i risultati solo se c'è una risposta -->
        <?php if ($risposta): ?>

            <!-- DOMANDA -->
            <div class="blocco domanda-utente">
                <span class="etichetta">DOMANDA</span>
                <p><?= htmlspecialchars($domanda) ?></p>
            </div>

            <!-- REASONING: mostrato SOLO SE il modello lo ha restituito -->
            <?php if ($reasoning): ?>
            <div class="blocco reasoning">
                <span class="etichetta">RAGIONAMENTO INTERNO</span>
                <p><?= nl2br(htmlspecialchars($reasoning)) ?></p>
            </div>
            <?php endif; ?>

            <!-- RISPOSTA -->
            <div class="blocco risposta-ai">
                <span class="etichetta">RISPOSTA</span>
                <p><?= nl2br(htmlspecialchars($risposta)) ?></p>
            </div>

            <!-- STRUTTURA COMPLETA DELLA RISPOSTA JSON -->
            <div class="blocco statistiche">
                <span class="etichetta">STRUTTURA DELLA RISPOSTA</span>
                <div class="stats-griglia">

                    <!-- Dati principali -->
                    <div class="stat">
                        <span class="stat-label">id</span>
                        <span class="stat-valore"><?= htmlspecialchars($json["id"]) ?></span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">object</span>
                        <span class="stat-valore"><?= htmlspecialchars($json["object"]) ?></span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">created</span>
                        <span class="stat-valore"><?= $json["created"] ?></span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">model</span>
                        <span class="stat-valore"><?= htmlspecialchars($json["model"]) ?></span>
                    </div>

                    <!-- choices[0] -->
                    <div class="stat">
                        <span class="stat-label">choices[0] → index</span>
                        <span class="stat-valore"><?= $json["choices"][0]["index"] ?></span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">choices[0] → message → role</span>
                        <span class="stat-valore"><?= htmlspecialchars($json["choices"][0]["message"]["role"]) ?></span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">choices[0] → finish_reason</span>
                        <span class="stat-valore"><?= htmlspecialchars($json["choices"][0]["finish_reason"]) ?></span>
                    </div>

                    <!-- usage -->
                    <div class="stat">
                        <span class="stat-label">usage → queue_time</span>
                        <span class="stat-valore"><?= $json["usage"]["queue_time"] ?></span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">usage → prompt_tokens</span>
                        <span class="stat-valore"><?= $json["usage"]["prompt_tokens"] ?></span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">usage → prompt_time</span>
                        <span class="stat-valore"><?= $json["usage"]["prompt_time"] ?></span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">usage → completion_tokens</span>
                        <span class="stat-valore"><?= $json["usage"]["completion_tokens"] ?></span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">usage → completion_time</span>
                        <span class="stat-valore"><?= $json["usage"]["completion_time"] ?></span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">usage → total_tokens</span>
                        <span class="stat-valore"><?= $json["usage"]["total_tokens"] ?></span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">usage → total_time</span>
                        <span class="stat-valore"><?= $json["usage"]["total_time"] ?></span>
                    </div>

                    <!-- reasoning_tokens: solo se presente -->
                    <?php if (!empty($json["usage"]["completion_tokens_details"]["reasoning_tokens"])): ?>
                    <div class="stat">
                        <span class="stat-label">completion_tokens_details → reasoning_tokens</span>
                        <span class="stat-valore"><?= $json["usage"]["completion_tokens_details"]["reasoning_tokens"] ?></span>
                    </div>
                    <?php endif; ?>

                    <!-- x_groq -->
                    <div class="stat">
                        <span class="stat-label">x_groq → id</span>
                        <span class="stat-valore"><?= htmlspecialchars($json["x_groq"]["id"] ?? "") ?></span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">x_groq → seed</span>
                        <span class="stat-valore"><?= htmlspecialchars($json["x_groq"]["seed"] ?? "") ?></span>
                    </div>

                    <!-- system_fingerprint e service_tier -->
                    <div class="stat">
                        <span class="stat-label">system_fingerprint</span>
                        <span class="stat-valore"><?= htmlspecialchars($json["system_fingerprint"] ?? "") ?></span>
                    </div>
                    <div class="stat">
                        <span class="stat-label">service_tier</span>
                        <span class="stat-valore"><?= htmlspecialchars($json["service_tier"] ?? "") ?></span>
                    </div>

                </div>
            </div>

        <?php endif; ?>

    </div>

</body>
</html>