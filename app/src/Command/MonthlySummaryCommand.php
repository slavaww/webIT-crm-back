<?php
/**
 * CLI command for counting time spends by client and month
 * 
 * For run use command:
 * CRON
 * 0 1 1 * * /usr/bin/php /path/to/project/bin/console app:aggregate-monthly-data
 */

namespace App\Command;

use App\Repository\TimeSetsRepository;
use App\Repository\TimeSpendRepository;
use App\Entity\TimeSets;
use App\Entity\Clients;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:aggregate-monthly-data',
    description: 'Собирает суммы по месяцам для каждого клиента и сохраняет их в таблицу TimeSets',
)]
class MonthlySummaryCommand extends Command
{
    public function __construct(
        private TimeSpendRepository $timeSpendRepository,
        private TimeSetsRepository $timeSetsRepository,
        private EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Получаем агрегированные данные из TimeSpend
        $data = $this->timeSpendRepository->getMonthlyClientSum();
        foreach ($data as $item) {
            
            // Проверить, нет ли уже такой записи для этого клиента и месяца
            $exists = $this->timeSetsRepository
            ->findOneBy([
                'year' => $item['year'],
                'month' => $item['month'],
                'client' => $item['client_id']
            ]);
            
            if ($exists) {
                // Обновляем существующую запись
                $exists->setTimeSpend((int)$item['totalSum']);
                continue;
            }

            // Получаем объект клиента
            $client = $this->em->getRepository(Clients::class)->find($item['client_id']);
            if (!$client) {
                continue; // Пропускаем, если клиент не найден
            }

            // Создаем новую запись итогов
            $summary = new TimeSets();
            $summary->setYear((int)$item['year']);
            $summary->setMonth((int)$item['month']);
            $summary->setTimeSpend((int)$item['totalSum']);
            $summary->setClient($client);
            $summary->setTimeSet(0); // Установите нужное значение или сделайте nullable

            $this->em->persist($summary);
        }

        $this->em->flush();

        $output->writeln(sprintf('Processed %d monthly summaries by client!', count($data)));
        return Command::SUCCESS;
    }
}

