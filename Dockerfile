# Use an official PHP image.
FROM php:8.2-cli

# Set user arguments for UID and GID
ARG UID=1004
ARG GID=1006

# Create a group and user with the specified GID and UID
RUN groupadd -g ${GID} dev-admin && \
    useradd -u ${UID} -g dev-admin -m dev-admin

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