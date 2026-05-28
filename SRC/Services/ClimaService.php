<?php
namespace Services;

class ClimaService
{
    private string $apiKey;
    private string $baseUrlActual;
    private string $baseUrlForecast;

    // Modificamos el constructor para recibir las dos URLs que configuró tu socio
    public function __construct(string $apiKey, string $baseUrlActual)
    {
        $this->apiKey = $apiKey;
        $this->baseUrlActual = $baseUrlActual;
        
        // Deducimos de forma inteligente la de forecast reemplazando /weather por /forecast
        $this->baseUrlForecast = str_replace('/weather', '/forecast', $baseUrlActual);
    }

    public function obtenerActual(array $params): array
    {
        // Usamos la URL exacta para el clima actual
        $url = $this->baseUrlActual . '?appid=' . $this->apiKey . '&units=metric&lang=es';
        
        if (isset($params['lat'], $params['lon'])) {
            $url .= "&lat={$params['lat']}&lon={$params['lon']}";
        } elseif (isset($params['ciudad'])) {
            $url .= "&q=" . urlencode($params['ciudad']);
        } else {
            return ['error' => true, 'codigo' => 400, 'mensaje' => 'Faltan parámetros'];
        }

        return $this->ejecutarConsultaCurl($url, true);
    }

    public function obtenerPronostico(array $params): array
    {
        // Usamos la URL exacta deducida para el pronóstico
        $url = $this->baseUrlForecast . '?appid=' . $this->apiKey . '&units=metric&lang=es';

        if (isset($params['lat'], $params['lon'])) {
            $url .= "&lat={$params['lat']}&lon={$params['lon']}";
        } elseif (isset($params['ciudad'])) {
            $url .= "&q=" . urlencode($params['ciudad']);
        } else {
            return ['error' => true, 'codigo' => 400, 'mensaje' => 'Faltan parámetros'];
        }

        $resultado = $this->ejecutarConsultaCurl($url, false);

        if ($resultado['error']) {
            return $resultado;
        }

        return [
            'error' => false,
            'data' => $resultado['data']['list'] ?? []
        ];
    }

    /**
     * Centraliza las peticiones cURL de forma segura en XAMPP
     */
    private function ejecutarConsultaCurl(string $url, bool $esActual): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $respuesta = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($respuesta === false || $error) {
            return ['error' => true, 'codigo' => 500, 'mensaje' => 'Error de conexión externa: ' . $error];
        }

        if ($httpCode !== 200) {
            return ['error' => true, 'codigo' => $httpCode, 'mensaje' => 'Error externo HTTP ' . $httpCode];
        }

        $datos = json_decode($respuesta, true);

        if (!$datos || (isset($datos['cod']) && $datos['cod'] != 200)) {
            return ['error' => true, 'codigo' => 500, 'mensaje' => $datos['message'] ?? 'Respuesta inválida de la API'];
        }

        if ($esActual) {
            return [
                'error' => false,
                'data' => [
                    'temperatura' => $datos['main']['temp'] ?? null,
                    'humedad'     => $datos['main']['humidity'] ?? null,
                    'viento'      => $datos['wind']['speed'] ?? null,
                    'descripcion' => $datos['weather'][0]['description'] ?? null,
                    'fuente'      => 'OpenWeatherMap',
                    'timestamp'   => date('Y-m-d H:i:s'),
                    'ubicacion'   => $datos['name'] ?? 'Desconocida',
                ]
            ];
        }

        return ['error' => false, 'data' => $datos];
    }
}