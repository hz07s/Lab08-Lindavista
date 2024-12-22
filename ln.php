<?php
/**
 * Procesa una consulta en lenguaje natural y genera la consulta SQL correspondiente
 */
class NaturalLanguageProcessor {
    private $conexion;
    private $palabras = [];
    private $tipos = [];
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    /**
     * Procesa la consulta y genera el SQL correspondiente
     */
    private function tokenizarConsulta($consulta) {
        // Convertir a minúsculas y eliminar caracteres especiales
        $consulta = mb_strtolower(trim($consulta));
        $consulta = preg_replace('/[^a-záéíóúñ\s]/u', '', $consulta);
        
        // Dividir en palabras
        $palabras = preg_split('/\s+/', $consulta);
        
        // Filtrar palabras vacías
        return array_filter($palabras, function($palabra) {
            return !in_array($palabra, ['un', 'una', 'el', 'la', 'los', 'las', 'en', 'con', 'de']);
        });
    }
    
    /**
     * Identifica las palabras en el diccionario
     */
    private function identificarPalabras($palabras) {
        $this->palabras = [];
        $this->tipos = [];
        
        foreach ($palabras as $palabra) {
            $sql = "SELECT palabra FROM ln_diccionario WHERE palabra = ?";
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param("s", $palabra);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $this->palabras[] = $palabra;
            }
        }
        
        return !empty($this->palabras);
    }
    
    /**
     * Encuentra el patrón que coincide con la secuencia de palabras
     */
    private function encontrarPatron() {
        $patron = implode(' ', $this->palabras);
        
        $sql = "SELECT patron, consultasql FROM ln_patrones WHERE patron = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("s", $patron);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc();
        }
        
        return null;
    }
    
    /**
     * Procesa la consulta completa
     */
    public function procesarConsulta($consulta, &$sqlResultante) {
        // Tokenizar la consulta
        $palabras = $this->tokenizarConsulta($consulta);
        
        // Identificar palabras válidas
        if (!$this->identificarPalabras($palabras)) {
            return false;
        }
        
        // Encontrar patrón coincidente
        $patron = $this->encontrarPatron();
        if (!$patron) {
            return false;
        }
        
        // Generar SQL
        $sqlResultante = $this->generarSQL($patron['consultasql']);
        return true;
    }
    
    /**
     * Genera la consulta SQL final
     */
    private function generarSQL($template) {
        $sql = $template;
        foreach ($this->palabras as $i => $palabra) {
            $sql = str_replace("%".($i+1), $palabra, $sql);
        }
        return $sql;
    }
}

/**
 * Función wrapper para mantener la compatibilidad con el código existente
 */
function procesa_consulta($consulta, $conexion, &$sql) {
    $processor = new NaturalLanguageProcessor($conexion);
    return $processor->procesarConsulta($consulta, $sql);
}