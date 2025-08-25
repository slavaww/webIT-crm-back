<?php
/**
 * CLI command for counting unspent time by client and month
 * 
 * For run use command:
 * CRON
 * 0 1 1 * * /usr/bin/php /path/to/project/bin/console app:aggregate-monthly-spent
 */

namespace App\Command;

use App\Repository\TimeSetsRepository;
use App\Entity\TimeSets;
use App\Entity\Clients;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:aggregate-monthly-spent',
    description: 'Подсчитывает потраченное время по месяцам для каждого клиента и вычисляет остаток и сохраняет их в таблицу TimeSets',
)]
class MonthlyCountSpentCommand extends Command
{
    public function __construct(
        private TimeSetsRepository $timeSetsRepository,
        private EntityManagerInterface $em
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Получаем агрегированные данные из TimeSpend
        $data = $this->timeSetsRepository->findTimeSetsWithTimeSpend();
        
        foreach ($data as $item) {
            
            // Проверяем записи для этого клиента и месяца
            $exists = $this->timeSetsRepository
            ->findOneBy([
                'year' => $item['year'],
                'month' => $item['month'],
                'client' => $item['client']
            ]);
            
            if ($exists) {
                $sets_time = $exists->getTimeSet();
                $spend_time = $exists->getTimeSpend();
                $exists->setUnspentTime($sets_time - $spend_time);
                continue;
            }
            
        }
        
        $this->em->flush();
        
        $output->writeln(sprintf('Processed %d monthly summaries by client!', count($data)));
        return Command::SUCCESS;
    }
}
