# prj_ionos_crons
Crons

#### comandos
- php backend/public/index.php service=cron.dbbackupservice
- tests:
    - php ./vendor/bin/phpunit ./tests
- En .bash_profile
```sys
cmd() {
    /path-to-file/cmd "$@"
}
```



#### to-do:
- tests con bd y alarmas
- tests de distiontso servicios
- tests envio emails
- envio de alerta por exceso de peticiones maliciosas (wp)
    - comprobar datos de esta ip atacante: 2607:5300:203:42eb::
    - quizas debo de comprobar la media de peticiones por segundo para definir una normalidad
    - cron de informes por ataques mensuales