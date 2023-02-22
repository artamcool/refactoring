<?php

declare(strict_types=1);

namespace Docler;

interface DoclerReporterInterface {
    public function collect();
    public function generate($rows);
    public static function report(EmailReporter $reporter, $subject, $message);
}

final class docler_reporter_manager implements DoclerReporterInterface {
    public const db_felhasznalonev = 'nagyontitkosfelhasznalonev';
    public const db_jelszo = 'nagyontitkosjelszo';
    public const db = 'reports';

    public $mysqli;

    public function __construct() {
        $this->mysqli = mysqli_connect('localhost', 'mysecretusername', 'mysecretpassword', "reports");
    }

    protected function __destruct() {
        $this->mysqli->close();
    }

    public function collect()
    {
        $limit = 10;
        $resultado = $this->mysqli->query("SELECT name, email, income FROM incomes ORDER BY id LIMIT $limit");

        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function generate($rows)
    {
        $body = '';
        foreach ($rows as $row) {
            $body .= $row['name'] . ' (' . $row['email'] . ') had ' . $row['income'] . ".\n";
        }

        return $body;
    }

    public static function report(EmailReporter $emailReporter, $subject, $message)
    {
        try {
            $emailReporter->report($subject, $message);
        } catch (Exception $exception) {
            // Log exception.
        } finally {
            echo "$message sent with subject \"$subject\".";
        }
    }
}

interface Recipient {public function __toString(): string;}
class LocalRecipient implements Recipient {
    public function __toString(): string
    {
        return '';
    }
}
class EmailRecipient implements Recipient {
    public function __toString(): string
    {
        return '';
    }
}

class Reporter {
    public function report(Recipient $recipient, $subject, $message): void {
        echo '';
    }
}

final class LocalReporter extends Reporter
{
    public function report(LocalRecipient $recipient, $subject, $message): void {
        echo "$subject: $message";
    }
}

final class EmailReporter extends Reporter
{
    public function report(EmailRecipient $recipient, $subject, $message): void {
        switch ($subject) {
            case 'Income report':
                mail($recipient->__toString(), reportSubjects::INCOME_REPORT, $message);
            case 'Payout report':
                mail($recipient->__toString(), reportSubjects::PAYOUT_REPORT, $message);
            case 'Annual report':
                mail($recipient->__toString(), reportSubjects::ANNUAL_REPORT, $message);
        }
    }
}

final class reportSubjects {
    public const INCOME_REPORT = 'Income report';
    public const PAYOUT_REPORT = 'Payout report';
    public const ANNUAL_REPORT = 'Annual report';
}

$reporter = new docler_reporter_manager();
//$reporter->mysqli = mysqli_connect('localhost', docler_reporter_manager::db_jelszo, docler_reporter_manager::db_felhasznalonev, docler_reporter_manager::db);
$result = $reporter->collect();
$result = $reporter->generate($result);
$reporter::report(new EmailReporter(), reportSubjects::INCOME_REPORT, $result);
