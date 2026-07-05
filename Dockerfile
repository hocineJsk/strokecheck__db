FROM php:8.2-apache

# Install system dependencies including Python and pip
RUN apt-get update && apt-get install -y \
    python3 \
    python3-pip \
    python3-venv \
    libmariadb-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache mod_rewrite and forcefully fix MPM error by deleting conflicting modules
RUN rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf \
    && ln -s /etc/apache2/mods-available/mpm_prefork.load /etc/apache2/mods-enabled/mpm_prefork.load \
    && ln -s /etc/apache2/mods-available/mpm_prefork.conf /etc/apache2/mods-enabled/mpm_prefork.conf \
    && a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . /var/www/html/

# Ensure the uploads directory exists and is writable
RUN mkdir -p /var/www/html/uploads && chown -R www-data:www-data /var/www/html/uploads

# Configure Apache to listen on the dynamic PORT provided by Railway
RUN echo 'Listen ${PORT}' > /etc/apache2/ports.conf \
    && sed -i 's/:80/:${PORT}/g' /etc/apache2/sites-available/000-default.conf

# Create a python virtual environment inside the container exactly where the PHP expects it
RUN python3 -m venv /var/www/html/venv

# Install Python requirements inside the virtual environment
RUN /var/www/html/venv/bin/pip install --no-cache-dir -r requirements.txt

# Ensure www-data can execute the python virtual environment
RUN chown -R www-data:www-data /var/www/html/venv

# Expose port 80
EXPOSE 80
