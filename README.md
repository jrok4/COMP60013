# COMP60013 — NBM Coursework Artefact

Configuration files, logs, test outputs and website source for the
COMP60013 Network and Browser Management coursework.

## Layout

```
configs/      Hardened service configurations (MySQL, NGINX, ModSecurity, SSH)
logs/         Sanitised excerpts of system logs
system/       Host info and firewall (UFW) state
tests/        Verification command output
website/      Web application source served by NGINX
```

Generated 2026-05-01T20:07:28+00:00 on `opopopop`.

## Additional test artefacts

tests/sqlmap/    sqlmap injection test against vulnerable endpoint
tests/apachebench/ ApacheBench load-test results
tests/ssh/       SSH public-key authentication evidence
tests/curl/      curl -vI output verifying HTTPS configuration
tests/database/  MySQL schema dump (no row data)

