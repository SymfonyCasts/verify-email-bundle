FROM nginx:alpine

RUN apk update && \
    apk add --no-cache openssl

COPY ./conf.d/default.conf /etc/nginx/conf.d/default.conf

RUN mkdir /etc/nginx/ssl

RUN openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/nginx/ssl/nginx-UNSECURE.key \
    -out /etc/nginx/ssl/nginx-UNSECURE.crt \
    -subj '/C=US/ST=FL/L=DevTown/O=devDeveloper/CN=localhost'

CMD ["nginx"]

EXPOSE 80 443