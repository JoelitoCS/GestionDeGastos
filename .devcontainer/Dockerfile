FROM php:8.0-cli

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    unzip \
    git \
    curl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Instalar extensiones PHP esenciales
RUN docker-php-ext-install \
    mysqli \
    pdo \
    pdo_mysql \
    mbstring \
    zip \
    xml \
    curl \
    opcache

# Habilitar extensiones
RUN docker-php-ext-enable \
    mysqli \
    pdo_mysql \
    opcache

# Configuración de PHP recomendada
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

# Configurar opcache para desarrollo
RUN echo "opcache.enable=1" >> "$PHP_INI_DIR/php.ini" \
    && echo "opcache.revalidate_freq=0" >> "$PHP_INI_DIR/php.ini" \
    && echo "display_errors=1" >> "$PHP_INI_DIR/php.ini" \
    && echo "error_reporting=E_ALL" >> "$PHP_INI_DIR/php.ini"

# Directorio de trabajo
WORKDIR /workspaces/GestionDeGastos

EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:8080", "-t", "/workspaces/GestionDeGastos"]