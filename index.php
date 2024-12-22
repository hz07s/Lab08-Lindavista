<?php
require_once 'db_config.php';
require_once 'ln.php';

class ViviendasSearch {
    private $db;
    private $mensaje = '';
    private $resultados = [];
    
    public function __construct() {
        $this->db = DatabaseConnection::getInstance()->getConnection();
    }
    
    public function procesarConsulta($consulta) {
        if (empty(trim($consulta))) {
            $this->mensaje = 'Debe introducir una consulta';
            return false;
        }
        
        $sql = '';
        if (procesa_consulta($consulta, $this->db, $sql)) {
            $result = $this->db->query($sql);
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $this->resultados[] = $row;
                }
                return true;
            } else {
                $this->mensaje = 'No hay viviendas disponibles';
                return false;
            }
        }
        
        $this->mensaje = 'La consulta no es correcta';
        return false;
    }
    
    public function getMensaje() {
        return $this->mensaje;
    }
    
    public function getResultados() {
        return $this->resultados;
    }
}

$search = new ViviendasSearch();
$consulta = $_POST['consulta'] ?? '';
$procesado = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $procesado = $search->procesarConsulta($consulta);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Búsqueda de Viviendas - Lindavista</title>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --error-color: #e74c3c;
            --success-color: #2ecc71;
            --background-color: #ecf0f1;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            background-color: var(--background-color);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .search-form {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .search-input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            margin-bottom: 1rem;
        }
        
        .search-button {
            background-color: var(--secondary-color);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        .search-button:hover {
            background-color: #2980b9;
        }
        
        .error-message {
            color: var(--error-color);
            margin-top: 1rem;
            padding: 10px;
            border-left: 4px solid var(--error-color);
            background-color: rgba(231, 76, 60, 0.1);
        }
        
        .results-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px 0;
        }
        
        .property-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .property-card h3 {
            color: var(--primary-color);
            margin-top: 0;
        }
        
        .property-info {
            margin: 10px 0;
        }
        
        .property-feature {
            display: flex;
            align-items: center;
            margin: 5px 0;
        }
        
        .price {
            font-size: 1.25rem;
            color: var(--secondary-color);
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Búsqueda de Viviendas Lindavista</h1>
        
        <div class="search-form">
            <form method="POST" action="">
                <input type="text" 
                       name="consulta" 
                       class="search-input"
                       placeholder="Ejemplo: Busco una casa en el centro" 
                       value="<?php echo htmlspecialchars($consulta); ?>">
                
                <button type="submit" class="search-button">Buscar</button>
                
                <?php if ($procesado && !empty($search->getMensaje())): ?>
                    <div class="error-message">
                        <?php echo htmlspecialchars($search->getMensaje()); ?>
                    </div>
                <?php endif; ?>
            </form>
        </div>

        <?php if ($procesado && !empty($search->getResultados())): ?>
            <div class="results-container">
                <?php foreach ($search->getResultados() as $vivienda): ?>
                    <div class="property-card">
                        <h3><?php echo htmlspecialchars($vivienda['tipo']); ?> en <?php echo htmlspecialchars($vivienda['zona']); ?></h3>
                        
                        <div class="property-info">
                            <div class="property-feature">
                                <span>Dormitorios: <?php echo htmlspecialchars($vivienda['ndormitorios']); ?></span>
                            </div>
                            <div class="property-feature">
                                <span>Metros: <?php echo htmlspecialchars($vivienda['metros']); ?> m²</span>
                            </div>
                            <div class="property-feature">
                                <span>Garaje: <?php echo $vivienda['garaje'] ? 'Sí' : 'No'; ?></span>
                            </div>
                            <div class="property-feature price">
                                <span><?php echo number_format($vivienda['precio'], 2, ',', '.'); ?> €</span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>