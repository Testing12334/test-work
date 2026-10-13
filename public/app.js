const form = document.querySelector('#focus-form');
const input = document.querySelector('#task');
const result = document.querySelector('#result');
try {
  const saved = localStorage.getItem('focus-task');
  if (saved) input.value = saved;
} catch { /* The form still works when browser storage is unavailable. */ }
form.addEventListener('submit', (event) => {
  event.preventDefault();
  const task = input.value.trim();
  if (!task) {
    input.setCustomValidity('Запиши задачу, чтобы выбрать свой фокус.');
    input.reportValidity();
    return;
  }
  try {
    localStorage.setItem('focus-task', task);
    result.textContent = `Твой фокус: ${task}. Начни с одного маленького шага.`;
  } catch {
    result.textContent = `Твой фокус: ${task}. Сохранение в браузере недоступно, но можно начать прямо сейчас.`;
  }
});
input.addEventListener('input', () => input.setCustomValidity(''));
