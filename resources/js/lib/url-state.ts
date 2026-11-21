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

export function getInitialAction(): { action: string | null; id: string | null } {
  const params = getQueryParams();
  const action = params.get("action");
  const rawId = params.get("id");
  const id = rawId !== null && rawId.trim() !== "" ? rawId.trim() : null;

  return { action, id };
}

export function setActionParams(action: string | null, id: string | null = null): void {
  updateQueryParams({ action, id });
}

export function restoreDialogAction<T extends { uid: string }>(
  items: T[],
  handlers: {
    onCreate?: () => void;
    onDetail?: (item: T) => void;
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

  if (action === "detail" && id !== null && handlers.onDetail) {
    const target = items.find((item) => item.uid === id);
    if (target) {
      handlers.onDetail(target);
      return;
    }
  }

  if (action === "edit" && id !== null && handlers.onEdit) {
    const target = items.find((item) => item.uid === id);
    if (target) {
      handlers.onEdit(target);
      return;
    }
  }

  if (action === "delete" && id !== null && handlers.onDelete) {
    const target = items.find((item) => item.uid === id);
    if (target) {
      handlers.onDelete(target);
      return;
    }
  }

  setActionParams(null, null);
}
