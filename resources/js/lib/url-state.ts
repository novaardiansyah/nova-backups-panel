export function getQueryParams(): URLSearchParams {
  if (typeof window === "undefined") {
    return new URLSearchParams();
  }
  return new URLSearchParams(window.location.search);
}

export function updateQueryParams(updates: Record<string, string | number | null>): void {
  if (typeof window === "undefined") return;
  const url = new URL(window.location.href);

  for (const [key, value] of Object.entries(updates)) {
    if (value === null || value === "") {
      url.searchParams.delete(key);
    } else {
      url.searchParams.set(key, String(value));
    }
  }

  window.history.replaceState(window.history.state, "", `${url.pathname}${url.search}${url.hash}`);
}

export function getInitialAction(): { action: string | null; id: number | null } {
  const params = getQueryParams();
  const action = params.get("action");
  const rawId = params.get("id");
  const parsedId = rawId !== null && rawId !== "" ? Number(rawId) : null;
  const id = parsedId !== null && Number.isInteger(parsedId) && parsedId > 0 ? parsedId : null;

  return { action, id };
}

export function setActionParams(action: string | null, id: number | null = null): void {
  updateQueryParams({ action, id });
}

export function restoreDialogAction<T extends { id: number }>(
  items: T[],
  handlers: {
    onCreate?: () => void;
    onEdit?: (item: T) => void;
    onDelete?: (item: T) => void;
  },
): void {
  const { action, id } = getInitialAction();
  if (!action) return;

  if (action === "create" && handlers.onCreate) {
    handlers.onCreate();
    return;
  }

  if (action === "edit" && id !== null && handlers.onEdit) {
    const target = items.find((item) => item.id === id);
    if (target) {
      handlers.onEdit(target);
      return;
    }
  }

  if (action === "delete" && id !== null && handlers.onDelete) {
    const target = items.find((item) => item.id === id);
    if (target) {
      handlers.onDelete(target);
      return;
    }
  }

  setActionParams(null, null);
}
