# COMP60013 — NBM Coursework Artefact

Configuration files, logs, test outputs and website source.

## Layout

```
configs/      Hardened service configurations (MySQL, NGINX, ModSecurity, SSH)
logs/         Sanitised excerpts of system logs
system/       Host info and firewall (UFW) state
tests/        Verification command output
website/      Web application source served by NGINX
```

Generated 2026-05-01T20:07:28+00:00 on `opopopop`.

## Additional Testing

tests/sqlmap/    sqlmap injection test (testing waf)
tests/apachebench/ ApacheBench load-test results
tests/ssh/       SSH public-key authentication evidence
tests/curl/      curl -vI output verifying HTTPS configuration
tests/database/  MySQL schema dump (no row data)

