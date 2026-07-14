ARG PLAYWRIGHT_DOCKER_IMAGE
FROM $PLAYWRIGHT_DOCKER_IMAGE

ARG KASMVNC_VERSION
ARG username
ARG uid
ARG gid

USER root

# Install kasmvnc for VNC access
RUN if [ -n "$KASMVNC_VERSION" ]; then \
      curl -fSL "https://github.com/kasmtech/KasmVNC/releases/download/v${KASMVNC_VERSION}/kasmvncserver_jammy_${KASMVNC_VERSION}_amd64.deb" -o /tmp/kasmvnc.deb && \
      apt-get install -y /tmp/kasmvnc.deb && \
      rm /tmp/kasmvnc.deb && \
      mkdir -p /home/$username/.vnc && \
      echo "password" | vncpasswd -f > /home/$username/.vnc/passwd && \
      chmod 600 /home/$username/.vnc/passwd; \
    fi

COPY kasmvnc.yaml /etc/kasmvnc/kasmvnc.yaml
COPY xstartup /home/$username/.vnc/xstartup
RUN chmod +x /home/$username/.vnc/xstartup

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

USER $username
