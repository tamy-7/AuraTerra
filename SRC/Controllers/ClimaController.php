<?php
declare(strict_types=1);

namespace Src\Controllers;

class ClimaController 
{
    // 🔑 Reemplazá esto con tu API Key real de OpenWeather
    private string $apiKey = '3dbd3ceab1f4f0c1727abc805e731d13'; 

    /**
     * Endpoint: /clima/actual
     * Retorna las condiciones climáticas del momento.
     */
    public function handleClimaActual(): void 
    {
        header('Content-Type: application/json');
        
        // 1. CAPTURAR PARÁMETROS ENVIADOS POR EL JAVASCRIPT
        $ciudad = $_GET['ciudad'] ?? null;
        $lat = $_GET['lat'] ?? null;
        $lon = $_GET['lon'] ?? null;

        // 2. CONSTRUIR URL DINÁMICA HACIA OPENWEATHER
        if ($lat !== null && $lon !== null && is_numeric($lat) && is_numeric($lon)) {
            $url = "https://api.openweathermap.org/data/2.5/weather?lat={$lat}&lon={$lon}&appid={$this->apiKey}&units=metric&lang=es";
        } elseif ($ciudad !== null && trim($ciudad) !== '') {
            // Como mandamos "Ciudad, Provincia, País", OpenWeather se puede confundir.
            // Limpiamos y tomamos solo el nombre de la ciudad real antes de la primera coma.
            $partes = explode(',', $ciudad);
            $ciudadLimpia = trim($partes[0]);
            $url = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($ciudadLimpia) . "&appid={$this->apiKey}&units=metric&lang=es";
        } else {
            // Resguardo por defecto si no se envía nada
            $url = "https://api.openweathermap.org/data/2.5/weather?q=Crespo&appid={$this->apiKey}&units=metric&lang=es";
        }

        // 3. CONSULTAR LA API EXTERNA
        $response = @file_get_contents($url);
        
        if ($response === false) {
            echo json_encode([
                "ok" => false, 
                "error" => "No se pudo obtener respuesta del servicio meteorológico externo."
            ]);
            exit;
        }

        $data = json_decode($response, true);

        if (!isset($data['main'])) {
            echo json_encode([
                "ok" => false, 
                "error" => "La localidad ingresada no pudo ser procesada por OpenWeather."
            ]);
            exit;
        }

        // 4. RESPUESTA ADAPTADA AL DASHBOARD
        $resultado = [
            "ok" => true,
            "data" => [
                "ubicacion" => $ciudad ?? $data['name'],
                "temperatura" => $data['main']['temp'],
                "descripcion" => $data['weather'][0]['description'] ?? 'Despejado',
                "humedad" => $data['main']['humidity'] ?? 0,
                "viento" => $data['wind']['speed'] ?? 0,
                "timestamp" => date("d/m/Y H:i:s")
            ]
        ];

        echo json_encode($resultado);
        exit;
    }

    /**
     * Endpoint: /clima/pronostico
     * Retorna la tendencia extendida de las próximas horas y días.
     */
    public function handleClimaPronostico(): void 
    {
        header('Content-Type: application/json');
        
        $ciudad = $_GET['ciudad'] ?? null;
        $lat = $_GET['lat'] ?? null;
        $lon = $_GET['lon'] ?? null;

        // CONSTRUIR URL DINÁMICA (forecast)
        if ($lat !== null && $lon !== null && is_numeric($lat) && is_numeric($lon)) {
            $url = "https://api.openweathermap.org/data/2.5/forecast?lat={$lat}&lon={$lon}&appid={$this->apiKey}&units=metric&lang=es";
        } elseif ($ciudad !== null && trim($ciudad) !== '') {
            $partes = explode(',', $ciudad);
            $ciudadLimpia = trim($partes[0]);
            $url = "https://api.openweathermap.org/data/2.5/forecast?q=" . urlencode($ciudadLimpia) . "&appid={$this->apiKey}&units=metric&lang=es";
        } else {
            $url = "https://api.openweathermap.org/data/2.5/forecast?q=Crespo&appid={$this->apiKey}&units=metric&lang=es";
        }

        $response = @file_get_contents($url);
        
        if ($response === false) {
            echo json_encode([
                "ok" => false, 
                "error" => "No se pudo obtener el pronóstico extendido."
            ]);
            exit;
        }

        $data = json_decode($response, true);

        if (!isset($data['list'])) {
            echo json_encode([
                "ok" => false, 
                "error" => "Estructura de pronóstico no encontrada."
            ]);
            exit;
        }

        // RETORNAR LA LISTA DIRECTA QUE LA FUNCIÓN consultarPronostico() RECORRE
        echo json_encode([
            "ok" => true,
            "data" => $data['list']
        ]);
        exit;
    }
}