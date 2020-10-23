<?php

namespace FosterMade\Rokanan\Task;

use FosterMade\Rokanan\Command\Command;
use Symfony\Component\Process\Process;

class TrustCertificateTask
{
    const CERT_DIR = '/usr/local/etc/ssl/certs';
    const COMMAND_CREATE_DIR = 'sudo mkdir -m 0755 -p %1$s && sudo chown $USER %1$s && sudo -u $USER mkdir -p %2$s';
    const COMMAND_COPY_CERT = 'ansible vagrant -m fetch -a "flat=true src=/etc/ssl/certs/%s.crt dest=%s/"';
    const COMMAND_VERIFY_CERT = 'security verify-cert -c %s';
    const COMMAND_TRUST_CERT = 'sudo security add-trusted-cert -d -k /Library/Keychains/System.keychain %s';

    public function runInContext(Command $context)
    {
        if (!file_exists(self::CERT_DIR) || !is_writable(self::CERT_DIR)) {
            $process = (new Process(sprintf(self::COMMAND_CREATE_DIR, dirname(dirname(self::CERT_DIR)), self::CERT_DIR)))->mustRun();
            if (!$process->isSuccessful()) {
                throw new \Exception($process->getErrorOutput());
            }
        }

        $hostname = $context->localVars['hostname'];

        $path = sprintf('%s/%s.crt', self::CERT_DIR, $hostname);

        $process = new Process(sprintf(self::COMMAND_COPY_CERT, $hostname, self::CERT_DIR));
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \Exception($process->getErrorOutput() ?: $process->getOutput());
        }

        $result = json_decode(explode('=>', $process->getOutput())[1], true);

        $trusted = false;
        if ($unchanged = (false === $result['changed'])) {
            $context->output->writeln('<info>The cert already exists on the host machine (md5sum match).</info>');
            $context->output->write('<info>Checking if the certificate is trusted . . . </info>');

            $process = new Process(sprintf(self::COMMAND_VERIFY_CERT, $path));
            $process->run();
            $out = ($trusted = $process->isSuccessful()) ? '✔' : '✘';
            $context->output->writeln("<info>{$out}</info>");
        }

        if (false === $trusted) {
            $process = (new Process(sprintf(self::COMMAND_TRUST_CERT, $path)))->mustRun();

            if (!$process->isSuccessful()) {
                throw new \Exception($process->getErrorOutput());
            }

            $context->output->writeln("<info>The cert for {$hostname} is now trusted.</info>");
        }

        return $process->getExitCode();
    }
}
