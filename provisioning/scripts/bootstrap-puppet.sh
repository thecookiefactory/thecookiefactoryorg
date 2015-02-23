cd /vagrant/provisioning/puppet

CFLAGS=-O0 apt-get install ruby-dev -y
if ! gem list librarian-puppet -i; then
    gem install librarian-puppet;
fi
librarian-puppet install
