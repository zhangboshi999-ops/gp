<?php

namespace app\admin\command;

use app\common\library\MarketSync;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\console\input\Argument;
use think\console\input\Option;

/**
 * 行情同步命令
 *
 * 用法：
 * 数据源：配置了 itick.token 时，日本 + 美国股票统一走 iTick；
 *         否则日股走 FMP/Alpha Vantage，美股走 Twelve Data（未配 key 时回落纳斯达克官方接口）。
 *
 *   php think sync quote                 同步股票最新报价（日本 + 美国）
 *   php think sync quote --market=TSE    只同步某个市场（TSE/NASDAQ/NYSE）
 *   php think sync index                 同步大盘指数
 *   php think sync kline --code=7203     同步指定标的日线
 *   php think sync crypto --kline=1      同步加密行情（含日线，CoinGecko）
 *   php think sync profile               同步基本面（市值/PE/PB/股息率）
 *   php think sync all                   全部同步
 *
 * crontab 示例（每个交易日收盘后同步一次，注意 iTick 免费版需逐只限速，全量约 2 分钟）：
 *   0 16 * * 1-5 cd /path/to/fastadmin && php think sync quote >> runtime/log/sync.log 2>&1
 *   0 6 * * 2-6 cd /path/to/fastadmin && php think sync all >> runtime/log/sync.log 2>&1
 */
class Sync extends Command
{
    protected function configure()
    {
        $this->setName('sync')
            ->addArgument('action', Argument::OPTIONAL, 'quote|kline|crypto|profile|index|all', 'all')
            ->addOption('code', null, Option::VALUE_OPTIONAL, '指定标的代码')
            ->addOption('kline', null, Option::VALUE_OPTIONAL, '是否同步K线(1/0)', 0)
            ->addOption('source', null, Option::VALUE_OPTIONAL, '数据源:twelvedata/coingecko', 'twelvedata')
            ->addOption('market', null, Option::VALUE_OPTIONAL, '仅同步某个市场:TSE/NASDAQ/NYSE')
            ->setDescription('同步外部行情数据(Twelve Data / CoinGecko)');
    }

    protected function execute(Input $input, Output $output)
    {
        $action = strtolower($input->getArgument('action') ?: 'all');
        $code = $input->getOption('code');
        $withKline = (int)$input->getOption('kline') === 1;

        $output->writeln('== 行情同步开始：' . date('Y-m-d H:i:s') . ' ==');

        switch ($action) {
            case 'quote':
                $market = $input->getOption('market');
                MarketSync::syncQuote($code ? [$code] : [], $market ? strtoupper($market) : '');
                break;
            case 'kline':
                if ($code) {
                    MarketSync::syncKline($code);
                } else {
                    $output->writeln('请指定 --code 参数');
                }
                break;
            case 'crypto':
                if (strtolower((string)$input->getOption('source')) === 'coingecko') {
                    // 备用源：CoinGecko 市值/排名/24h高低
                    MarketSync::syncCryptoMarket();
                    break;
                }
                MarketSync::syncCrypto();
                if ($withKline) {
                    foreach (['BTC', 'ETH', 'USDT'] as $item) {
                        MarketSync::syncCryptoKline($item);
                    }
                }
                break;
            case 'profile':
                MarketSync::syncProfile($code ? [$code] : []);
                break;
            case 'index':
                MarketSync::syncIndex();
                break;
            case 'all':
            default:
                MarketSync::syncAll();
                break;
        }

        foreach (MarketSync::messages() as $message) {
            $output->writeln('  ' . $message);
        }
        $output->writeln('== 完成，消耗接口额度：' . MarketSync::credits() . ' credits ==');
    }
}
