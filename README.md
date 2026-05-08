# VetNutri AI - Sistema Avanzado de Nutrición Veterinaria

VetNutri AI es una plataforma clínica diseñada para médicos veterinarios que automatiza el cálculo, formulación y redacción de planes nutricionales terapéuticos, con un enfoque especializado en dietas renales (IRIS I-IV).

El sistema combina **matemática de precisión basada en los requerimientos del NRC (National Research Council)** para calcular aportes nutricionales exactos según el peso metabólico del paciente, y utiliza **Inteligencia Artificial (Google Gemini)** de forma estrictamente controlada para redactar justificaciones clínicas y guías de preparación altamente profesionales.

## 🌟 Características Principales

*   **Motor de Cálculo Clínico:** Algoritmo secuencial que formula raciones precisas asegurando mínimos de Alto Valor Biológico (HBV) y límites estrictos de Fósforo, Sodio y Potasio según el estadio IRIS del paciente.
*   **Ajustes Metabólicos Dinámicos:** Calcula requerimientos energéticos (RER y MER) ajustados por edad (>7 años), estado reproductivo (castrado/entero) y nivel de actividad.
*   **Reportes PDF Nativos:** Exportación de historiales dietéticos a PDF utilizando motores Chromium headless, garantizando fidelidad gráfica (Tailwind CSS) en documentos listos para entregar al propietario.
*   **Validación de Deficiencias:** Semáforo nutricional visual (Adecuado, Leve, Moderado, Crítico) que compara el aporte de la receta casera contra las tablas NRC 15-5.
*   **Redacción Asistida por IA:** Redacción de instrucciones de preparación y justificación nefrológica mediante Gemini AI, restringida por el motor matemático subyacente para evitar alucinaciones numéricas.

## 🛠 Stack Tecnológico

*   **Backend:** PHP 8.2+ / Laravel 12
*   **Frontend:** Blade / Tailwind CSS / Alpine.js 3 / Livewire 4
*   **Base de Datos:** MySQL 8
*   **PDF Engine:** Spatie Laravel PDF (Puppeteer / Browsershot)
*   **IA:** Google Gemini (gemini-2.0-flash)

---

## 🚀 Guía de Despliegue (Docker Estándar)

Esta guía explica cómo levantar la aplicación utilizando comandos puros de Docker Compose, ideal para entornos donde no se utiliza el wrapper de Laravel Sail.

### 1. Clonar el Repositorio

```bash
git clone <url-del-repositorio> veterinario-app
cd veterinario-app
```

### 2. Configurar Variables de Entorno

Copia el archivo de ejemplo para crear tu entorno local/producción:

```bash
cp .env.example .env
```

Abre el archivo `.env` y asegúrate de configurar las credenciales de base de datos y la API Key de Gemini:

```env
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=veterinario_app
DB_USERNAME=sail
DB_PASSWORD=password

GEMINI_API_KEY=tu_clave_api_aqui
```

### 3. Levantar los Contenedores

Construye y levanta los servicios definidos en el `compose.yaml` (Aplicación y MySQL) en segundo plano:

```bash
docker compose up -d --build
```

### 4. Instalar Dependencias de PHP (Composer)

Ejecuta Composer dentro del contenedor de la aplicación (`laravel.test`):

```bash
docker compose exec laravel.test composer install --no-interaction --optimize-autoloader
```

### 5. Generar la Clave de la Aplicación

```bash
docker compose exec laravel.test php artisan key:generate
```

### 6. Migrar y Poblar la Base de Datos (Seeders)

Este paso es **crítico**, ya que poblará la base de datos con la tabla de ingredientes fundamentales (`IngredientSeeder`):

```bash
docker compose exec laravel.test php artisan migrate --seed
```

### 7. Configurar Dependencias del Frontend y PDF (Node.js)

Instala los paquetes NPM y el binario de Chromium necesario para que el sistema de exportación a PDF funcione correctamente dentro del contenedor:

```bash
# Instalar paquetes NPM
docker compose exec laravel.test npm install

# Descargar el binario de Chrome Headless para la librería Spatie PDF
docker compose exec laravel.test npx puppeteer browsers install chrome-headless-shell

# Compilar los assets del frontend (para entorno de producción)
docker compose exec laravel.test npm run build
```

*(Nota: Si estás desarrollando localmente, en lugar de `npm run build` puedes ejecutar `docker compose exec laravel.test npm run dev`)*

### 8. Permisos de Almacenamiento

Asegúrate de que los directorios de caché y almacenamiento tengan los permisos correctos:

```bash
docker compose exec laravel.test chmod -R 775 storage bootstrap/cache
```

### ✅ ¡Listo!

La aplicación ahora debería estar disponible en [http://localhost](http://localhost) (o el puerto configurado en tu `.env`).
