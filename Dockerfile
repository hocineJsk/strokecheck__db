FROM php:8.2-apache

# Install system dependencies including Python and pip
RUN apt-get update && apt-get install -y \
    python3 \
    python3-pip \
    python3-venv \
    libmariadb-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache mod_rewrite and fix MPM error
RUN a2dismod mpm_event mpm_worker || true \
    && a2enmod mpm_prefork || true \
    && a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . /var/www/html/

# Ensure the uploads directory exists and is writable
RUN mkdir -p /var/www/html/uploads && chown -R www-data:www-data /var/www/html/uploads

# Create a python virtual environment inside the container exactly where the PHP expects it
RUN python3 -m venv /var/www/html/venv

# Install Python requirements inside the virtual environment
RUN /var/www/html/venv/bin/pip install --no-cache-dir -r requirements.txt

# Ensure www-data can execute the python virtual environment
RUN chown -R www-data:www-data /var/www/html/venv

# Expose port 80
EXPOSE 80
