# Generate the HTML output.
FROM markstory/cakephp-docs-builder as builder

COPY docs /data/docs

RUN cd /data/docs-builder && \
  make website LANGS="en es fr ja pt ru" SOURCE=/data/docs DEST=/data/website/

# Build a small nginx container with just the static site in it.
FROM nginx:1.15-alpine

COPY --from=builder /data/website /data/website
COPY --from=builder /data/docs-builder/nginx.conf /etc/nginx/conf.d/default.conf

# Move each version into place
RUN mv /data/website/html/ /usr/share/nginx/html/
