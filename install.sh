#!/bin/bash

# Check if Composer is installed
if ! command -v composer &> /dev/null; then
    echo "Composer is not installed. Please install Composer first."
    echo "Visit https://getcomposer.org/download/ for installation instructions."
    exit 1
fi

# Install dependencies
composer install

echo "CSV Processor plugin has been installed successfully!" 