symfony () {
    php bin/console "$1";
}

phpunit () {
    php bin/phpunit "$1";
}

alias la="ls -lah"
alias phpunit="vendor/bin/phpunit"
alias stan="vendor/bin/phpstan analyze -c phpstan.neon"