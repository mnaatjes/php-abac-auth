# Use an official PHP image.
FROM php:8.2-cli

# Set the working directory
WORKDIR /app

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# By default, the container will start and wait. The docker-compose.yml
# will mount the project directory here and is used to run commands.

#CMD ["tail", "-f", "/dev/null"]