export function getContextMenuStyle(event: MouseEvent, menuWidth = 160, menuHeight = 88): string {
  const x = Math.min(event.clientX, window.innerWidth - menuWidth - 8);
  const y = Math.min(event.clientY, window.innerHeight - menuHeight - 8);
  return `left: ${Math.max(8, x)}px; top: ${Math.max(8, y)}px;`;
}

export function getButtonMenuStyle(target: HTMLElement, menuWidth = 160, menuHeight = 88): string {
  const rect = target.getBoundingClientRect();
  const x = Math.min(rect.right - menuWidth, window.innerWidth - menuWidth - 8);
  const openUpward = window.innerHeight - rect.bottom < menuHeight + 12 && rect.top > menuHeight + 12;
  const y = openUpward ? rect.top - menuHeight - 4 : rect.bottom + 4;
  return `left: ${Math.max(8, x)}px; top: ${Math.max(8, y)}px;`;
}
