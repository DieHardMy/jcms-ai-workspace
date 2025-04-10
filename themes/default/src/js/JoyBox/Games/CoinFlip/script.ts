export {}; // Делаем файл модулем

declare global {
  interface Promise<T> {
    finally(callback?: () => void): Promise<T>;
}
}

// Константы для стилей
const COIN_STYLES = {
  heads: 'heads',
  tails: 'tails',
  flipping: 'flipping',
  neutral: 'neutral'
};

type CoinSide = typeof COIN_STYLES.heads | typeof COIN_STYLES.tails;

document.addEventListener('DOMContentLoaded', () => {
  console.log('[CoinFlip] Script loaded');

  const SERVER_TIMEOUT = 20000;
  let localTossCount = 0; // Локальный счетчик бросков

  // Добавляем типы для элементов DOM
  const coin = document.getElementById('coin') as HTMLDivElement | null;
  const tossCoinButton = document.getElementById('tossCoin') as HTMLButtonElement | null;
  const resultDiv = document.getElementById('result') as HTMLDivElement | null;
  const totalCoinTossesSpan = document.getElementById('total_coin_tosses') as HTMLSpanElement | null;

  let isProcessing = false;
  let tossResult: TossResult | null = null;

  const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null;
  const csrfToken = csrfTokenMeta?.getAttribute('content');

  // Инициализация счетчика при загрузке
  if (totalCoinTossesSpan) {
    localTossCount = parseInt(totalCoinTossesSpan.textContent || '0', 10);
  }

  coin?.addEventListener('animationend', () => {
    console.log('[CoinFlip] Animation ended');

    if (tossResult) {
      handleResult(tossResult);
    }

    tossResult = null;
    isProcessing = false;
    tossCoinButton?.removeAttribute('disabled');
  });

  function tossCoin() {
    console.log('[CoinFlip] Toss initiated');
    if (isProcessing) return;

    isProcessing = true;
    tossCoinButton?.setAttribute('disabled', 'true');
    resetCoinStyles();
    localTossCount++;

    if (coin) {
      coin.classList.add(COIN_STYLES.flipping);
    }

    const controller = new AbortController();
    const timeoutId = setTimeout(() => {
      controller.abort();
    }, SERVER_TIMEOUT);

    fetch('api/', {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': csrfToken || '',
        'Content-Type': 'application/json'
      },
      signal: controller.signal
    })
      .then(response => {
        if (!response.ok) throw new Error(response.status.toString());
        return response.json();
      })
      .then((data: TossResponse) => {
        // Корректируем счетчик только если серверное значение больше
        if (data.total_coin_tosses && data.total_coin_tosses > localTossCount) {
          localTossCount = data.total_coin_tosses;
        }

        tossResult = {
          side: data.side === 0 ? COIN_STYLES.heads : COIN_STYLES.tails,
          message: data.message || '',
          total_coin_tosses: localTossCount
        };
      })
      .catch(error => {
        console.error('[CoinFlip] Fetch error:', error);
        let side: CoinSide = Math.random() < 0.5 ? COIN_STYLES.heads : COIN_STYLES.tails;

        tossResult = {
          side: side,
          message: side === COIN_STYLES.heads ? 'Your result is — heads' : 'Your result is — tails',
          total_coin_tosses: localTossCount
        };
      })
      .finally(() => {
        clearTimeout(timeoutId);
      });
  }

  function handleResult(data: TossResult) {
    console.log('[CoinFlip] Handling result:', data);
    if (!coin || !resultDiv) return;

    coin.classList.remove(COIN_STYLES.heads, COIN_STYLES.tails, COIN_STYLES.flipping, COIN_STYLES.neutral);
    coin.classList.add(data.side === COIN_STYLES.heads ? COIN_STYLES.heads : COIN_STYLES.tails);

    resultDiv.textContent = data.message ?? '';
    resultDiv.classList.toggle('hidden', !data.message);

    if (data.total_coin_tosses && totalCoinTossesSpan) {
      totalCoinTossesSpan.textContent = data.total_coin_tosses.toString();
    }
  }


  function resetCoinStyles() {
    console.log('[CoinFlip] Resetting styles');
    if (!coin || !resultDiv) return;

    resultDiv.textContent = '???';
    coin.classList.remove(COIN_STYLES.heads, COIN_STYLES.tails, COIN_STYLES.flipping, COIN_STYLES.neutral);
    coin.classList.add(COIN_STYLES.neutral);
  }


  console.log('[CoinFlip] Adding event listeners');
  coin?.addEventListener('click', tossCoin);
  tossCoinButton?.addEventListener('click', tossCoin);
  console.log('[CoinFlip] Event listeners added');
});



// Интерфейсы и типы
interface TossResponse {
  side: number;
  message?: string;
  total_coin_tosses?: number;
}

type TossResult = {
  side: CoinSide;
  message?: string;
  total_coin_tosses?: number | null;
};
