<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fonction pour obtenir les informations de géolocalisation à partir de l'API ipinfo.io
function getGeolocation($ip)
{
    $url = "http://ipinfo.io/{$ip}/json";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Erreur cURL : ' . curl_error($ch);
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode != 200) {
        echo "Erreur HTTP : Code {$httpCode}<br>";
    }

    curl_close($ch);

    return $response ? json_decode($response, true) : null;
}

$clientIp = $_SERVER['REMOTE_ADDR'];

if ($clientIp === '172.20.0.1') {
    $clientIp = '193.50.135.203';
}

// Récupérer les données de géolocalisation pour l'adresse IP du client
$geoData = getGeolocation($clientIp);

if ($geoData) {
    // Vérifier si l'utilisateur se trouve à Nancy
    if (isset($geoData['city']) && strtolower($geoData['city']) === 'nancy') {
        echo "L'utilisateur est à Nancy.<br>";
        echo "Détails : <br>";
        echo "Ville : {$geoData['city']}<br>";
        echo "Région : {$geoData['region']}<br>";
        echo "Pays : {$geoData['country']}<br>";
        echo "Latitude, Longitude : {$geoData['loc']}<br>"; // La location est sous forme "lat,long"
        
        // Récupérer latitude et longitude pour l'API météo
        list($latitude, $longitude) = explode(',', $geoData['loc']);
    } else {
        // Si l'utilisateur n'est pas à Nancy, afficher les informations disponibles
        echo "L'utilisateur n'est pas à Nancy. Voici sa localisation approximative :<br>";

        // Vérification de l'existence des données avant de les afficher
        echo isset($geoData['city']) ? "Ville : {$geoData['city']}<br>" : "Ville : Inconnue<br>";
        echo isset($geoData['region']) ? "Région : {$geoData['region']}<br>" : "Région : Inconnue<br>";
        echo isset($geoData['country']) ? "Pays : {$geoData['country']}<br>" : "Pays : Inconnu<br>";
        echo isset($geoData['loc']) ? "Location : {$geoData['loc']}<br>" : "Location : Inconnue<br>";

        // Récupérer latitude et longitude pour l'API météo
        list($latitude, $longitude) = explode(',', $geoData['loc']);
    }

    // Appel à l'API météo avec les coordonnées géographiques
    $meteo_url = "http://www.infoclimat.fr/public-api/gfs/xml?_ll=48.67103,6.15083&_auth=BR9fSAR6UXMFKFNkUyVQeVE5VWBcKgIlA39WNQhtVClSOQJjDm5VM14wVitSfQI0UXwAY1phUmIAa1YuCnhUNQVvXzMEb1E2BWpTNlN8UHtRf1U0XHwCJQNhVjgIZlQpUjQCZg5zVTZeMlYxUnwCNFFiAGNaelJ1AGJWNgpkVDEFYV8zBGBRNAVjUzNTfFB7UWRVMlxrAj8DZFY2CGBUN1JiAjAOO1U0XmRWPFJ8AjVRYABgWmZSbABlVjgKZVQoBXlfQgQUUS4FKlNzUzZQIlF%2FVWBcPQJu&_c=8c5cc1a54781fbecdfafccc538dd6da0";

    // Charger le fichier XML
    $xml = new DOMDocument();
    $xml->load($meteo_url);
    
    // Charger le fichier XSL
    $xsl = new DOMDocument();
    $xsl->load('meteo-data-html.xsl'); // Assure-toi que ce chemin est correct
    
    // Créer un processeur XSLT
    $xsltProcessor = new XSLTProcessor();
    $xsltProcessor->importStylesheet($xsl);
    
    // Appliquer la transformation
    $htmlOutput = $xsltProcessor->transformToXML($xml);
    
    // Afficher le contenu généré en HTML
    echo $htmlOutput;
} else {
    echo "Impossible de récupérer les données de géolocalisation.<br>";
}

?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Météo et Circulation - Nancy</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
</head>

<body>
    <div id="map" style="height: 400px;"></div>

    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
        // Utilisation des variables PHP pour la latitude et la longitude
        var latitude = <?php echo $latitude; ?>;
        var longitude = <?php echo $longitude; ?>;

        // Création de la carte avec les coordonnées obtenues
        var map = L.map('map').setView([latitude, longitude], 13);

        // Chargement des tuiles OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

        // Icônes personnalisées pour différents types de trafic
        var icons = {
            accident: L.icon({
                iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
                iconSize: [32, 32],
                iconAnchor: [16, 32],
                popupAnchor: [0, -32]
            }),
            traffic: L.icon({
                iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
                iconSize: [32, 32],
                iconAnchor: [16, 32],
                popupAnchor: [0, -32]
            }),
            roadblock: L.icon({
                iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
                iconSize: [32, 32],
                iconAnchor: [16, 32],
                popupAnchor: [0, -32]
            })
        };

        // Marquer la position de l'utilisateur sur la carte
        var clientIcon = L.icon({
            iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-icon.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.7.1/images/marker-shadow.png',
            shadowSize: [41, 41]
        });

        // Marquer la position de l'utilisateur sur la carte
        L.marker([latitude, longitude], {
            icon: clientIcon
        }).addTo(map).bindPopup('Votre position');

        // Charger les données de trafic à partir de l'URL JSON
        fetch('https://carto.g-ny.org/data/cifs/cifs_waze_v2.json')
            .then(response => response.json())
            .then(data => {
                // Parcourir les données de trafic et ajouter des marqueurs sur la carte
                data.features.forEach(function(feature) {
                    var lat = feature.geometry.coordinates[1];  // Latitude
                    var lng = feature.geometry.coordinates[0];  // Longitude
                    var description = feature.properties.description || "Aucun détail disponible";
                    var type = feature.properties.type || "traffic"; // Type d'incident (par exemple, "traffic", "accident", etc.)

                    var icon = icons[type] || icons.traffic; // Utiliser l'icône traffic par défaut

                    var marker = L.marker([lat, lng], { icon: icon }).addTo(map);
                    marker.bindPopup(description);
                });
            });
    </script>
</body>

</html>
