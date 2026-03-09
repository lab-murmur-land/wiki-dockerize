FROM mariadb:latest

# COPY ./scripts/* /tmp/

HEALTHCHECK --interval=30s --timeout=5s --retries=3 \
  CMD mariadb-admin ping -h localhost || exit 1
