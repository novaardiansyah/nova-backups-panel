<script lang="ts">
  import { onMount } from "svelte";
  import { router, useForm } from "@inertiajs/svelte";
  import { fly, slide } from "svelte/transition";
  import { Button } from "@/components/ui/button/index.js";
  import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card/index.js";
  import { Input } from "@/components/ui/input/index.js";
  import { Label } from "@/components/ui/label/index.js";
  import type { BackupStorageProviderItem } from "@/lib/backup-types.js";
  import { restoreDialogAction, setActionParams } from "@/lib/url-state.js";

  interface KeyRow {
    key: string;
    value: string;
  }

  interface Props {
    providers: BackupStorageProviderItem[];
    onstatus?: (message: string) => void;
  }

  let { providers, onstatus }: Props = $props();

  let isDialogOpen = $state(false);
  let editingId = $state<number | null>(null);
  let deletingId = $state<number | null>(null);
  let keyRows = $state<KeyRow[]>([]);
  let menuProvider = $state<BackupStorageProviderItem | null>(null);
  let menuStyle = $state("");
  let menuEl = $state<HTMLDivElement | null>(null);
  let confirmDeleteProvider = $state<BackupStorageProviderItem | null>(null);

  const form = useForm({
    name: "",
    slug: "",
    keys: {} as Record<string, string>,
  });

  function slugify(value: string): string {
    return value
      .toLowerCase()
      .trim()
      .replace(/[^a-z0-9]+/g, "-")
      .replace(/^-+|-+$/g, "");
  }

  function addKeyRow() {
    keyRows = [...keyRows, { key: "", value: "" }];
  }

  function removeKeyRow(index: number) {
    keyRows = keyRows.filter((_, i) => i !== index);
  }

  function buildKeysObject(): Record<string, string> {
    const result: Record<string, string> = {};
    for (const row of keyRows) {
      const k = row.key.trim();
      if (k !== "") {
        result[k] = row.value;
      }
    }
    return result;
  }

  onMount(() => {
    restoreDialogAction(providers, {
      onCreate: openCreate,
      onEdit: openEdit,
      onDelete: (provider) => {
        confirmDeleteProvider = provider;
      },
    });
  });

  function openCreate() {
    editingId = null;
    form.reset();
    form.clearErrors();
    keyRows = [];
    isDialogOpen = true;
    setActionParams("create");
  }

  function openEdit(provider: BackupStorageProviderItem) {
    editingId = provider.id;
    form.clearErrors();
    form.name = provider.name;
    form.slug = provider.slug;
    keyRows = Object.entries(provider.keys ?? {}).map(([key, value]) => ({
      key,
      value: String(value ?? ""),
    }));
    isDialogOpen = true;
    setActionParams("edit", provider.uid);
  }

  function closeDialog() {
    isDialogOpen = false;
    editingId = null;
    form.reset();
    form.clearErrors();
    keyRows = [];
    setActionParams(null);
  }

  function handleSubmit(event: SubmitEvent) {
    event.preventDefault();
    form.keys = buildKeysObject();

    const options = {
      preserveScroll: true,
      onSuccess: (page: { props: unknown }) => {
        const nextStatus = (page.props as { status?: string | null }).status;
        if (nextStatus) {
          onstatus?.(nextStatus);
        }
        closeDialog();
      },
    };

    if (editingId !== null) {
      form.put(`/client/backup-storage-providers/${editingId}`, options);
      return;
    }

    form.post("/client/backup-storage-providers", options);
  }

  function handleDelete(id: number) {
    deletingId = id;
    router.delete(`/client/backup-storage-providers/${id}`, {
      preserveScroll: true,
      onSuccess: (page) => {
        const nextStatus = (page.props as { status?: string | null }).status;
        if (nextStatus) {
          onstatus?.(nextStatus);
        }
        if (editingId === id) {
          closeDialog();
        }
      },
      onFinish: () => {
        deletingId = null;
        confirmDeleteProvider = null;
        setActionParams(null);
      },
    });
  }
  function openContextMenu(event: MouseEvent, provider: BackupStorageProviderItem) {
    event.preventDefault();
    const menuWidth = 160;
    const menuHeight = 88;
    const x = Math.min(event.clientX, window.innerWidth - menuWidth - 8);
    const y = Math.min(event.clientY, window.innerHeight - menuHeight - 8);
    menuStyle = `left: ${Math.max(8, x)}px; top: ${Math.max(8, y)}px;`;
    menuProvider = provider;
  }

  function openButtonMenu(event: MouseEvent, provider: BackupStorageProviderItem) {
    event.stopPropagation();
    if (menuProvider?.id === provider.id) {
      closeMenu();
      return;
    }
    const target = event.currentTarget as HTMLElement;
    const rect = target.getBoundingClientRect();
    const menuWidth = 160;
    const menuHeight = 88;
    const x = Math.min(rect.right - menuWidth, window.innerWidth - menuWidth - 8);
    const openUpward = window.innerHeight - rect.bottom < menuHeight + 12 && rect.top > menuHeight + 12;
    const y = openUpward ? rect.top - menuHeight - 4 : rect.bottom + 4;
    menuStyle = `left: ${Math.max(8, x)}px; top: ${Math.max(8, y)}px;`;
    menuProvider = provider;
  }

  function closeMenu() {
    menuProvider = null;
  }

  function handleMenuEdit() {
    if (!menuProvider) return;
    const provider = menuProvider;
    closeMenu();
    openEdit(provider);
  }

  function handleMenuDelete() {
    if (!menuProvider) return;
    const provider = menuProvider;
    closeMenu();
    confirmDeleteProvider = provider;
    setActionParams("delete", provider.uid);
  }

  function closeConfirmDelete() {
    if (deletingId !== null) return;
    confirmDeleteProvider = null;
    setActionParams(null);
  }

  function confirmDelete() {
    if (!confirmDeleteProvider) return;
    handleDelete(confirmDeleteProvider.id);
  }

  function handleWindowClick(event: MouseEvent) {
    if (!menuProvider) return;
    const target = event.target as Node | null;
    if (menuEl?.contains(target)) return;
    closeMenu();
  }
</script>

<svelte:window
  onclick={handleWindowClick}
  onkeydown={(e) => {
    if (e.key === "Escape") {
      if (menuProvider) {
        closeMenu();
        return;
      }
      if (confirmDeleteProvider) {
        closeConfirmDelete();
        return;
      }
      if (isDialogOpen) {
        closeDialog();
      }
    }
  }}
  onresize={() => {
    if (menuProvider) closeMenu();
  }}
  onscroll={() => {
    if (menuProvider) closeMenu();
  }}
/>

<div class="space-y-4">
  <div class="rounded-xl border border-line bg-page-elevated p-5 sm:p-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
      <div class="flex items-start gap-3">
        <span
          class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md border border-line bg-page text-accent"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-5">
            <ellipse cx="12" cy="5" rx="9" ry="3"/>
            <path d="M3 5v14a9 3 0 0 0 18 0V5"/>
            <path d="M3 12a9 3 0 0 0 18 0"/>
          </svg>
        </span>
        <div class="min-w-0">
          <h2 class="text-lg font-semibold tracking-tight text-ink">Backup Storage Providers</h2>
          <p class="mt-1 text-sm leading-6 text-muted">
            Define storage provider templates and default configuration keys.
          </p>
        </div>
      </div>

      <Button type="button" onclick={openCreate} class="shrink-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="mr-1.5 size-4">
          <line x1="12" y1="5" x2="12" y2="19"/>
          <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Add Provider
      </Button>
    </div>
  </div>

  <div class="rounded-xl border border-line bg-page-elevated p-5 sm:p-6">
    {#if providers.length === 0}
      <div class="rounded-lg border border-dashed border-line p-8 text-center">
        <p class="text-sm font-medium text-ink">No storage providers configured yet.</p>
        <p class="mt-1 text-xs text-muted">Create a provider template to get started.</p>
        <div class="mt-4">
          <Button type="button" size="sm" onclick={openCreate}>
            Add Provider
          </Button>
        </div>
      </div>
    {:else}
      <div role="list" class="space-y-3">
        {#each providers as provider (provider.id)}
          <div
            role="listitem"
            oncontextmenu={(e) => openContextMenu(e, provider)}
            class="flex items-center justify-between gap-4 rounded-lg border p-4 transition-colors duration-100 {menuProvider?.id === provider.id
              ? 'border-accent/50 bg-surface-muted/40'
              : 'border-line bg-page hover:border-line-strong'}"
          >
            <div class="min-w-0 space-y-1">
              <p class="truncate text-sm font-semibold text-ink">{provider.name}</p>
              <p class="text-xs text-muted">
                Slug: <span class="font-mono">{provider.slug}</span> &middot; Storages: {provider.storagesCount} &middot; Keys: {Object.keys(provider.keys ?? {}).length}
              </p>
            </div>

            <Button
              type="button"
              variant="ghost"
              size="icon"
              aria-label="Provider actions"
              aria-haspopup="menu"
              aria-expanded={menuProvider?.id === provider.id}
              onclick={(e) => openButtonMenu(e, provider)}
              class="shrink-0 text-muted hover:text-ink"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4">
                <circle cx="12" cy="5" r="1"/>
                <circle cx="12" cy="12" r="1"/>
                <circle cx="12" cy="19" r="1"/>
              </svg>
            </Button>
          </div>
        {/each}
      </div>
    {/if}
  </div>
</div>

{#if isDialogOpen}
  <div
    role="dialog"
    aria-modal="true"
    aria-labelledby="provider-dialog-title"
    tabindex="-1"
    class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4"
  >
    <button
      type="button"
      class="fixed inset-0 bg-black/50 backdrop-blur-xs"
      onclick={closeDialog}
      aria-label="Close modal"
      tabindex="-1"
    ></button>

    <div class="relative z-10 my-8 w-full max-w-4xl">
      <Card class="shadow-lg max-h-[90vh] flex flex-col">
        <CardHeader class="shrink-0">
          <div class="flex items-center justify-between gap-2">
            <CardTitle id="provider-dialog-title">
              {editingId !== null ? "Edit Storage Provider" : "Add Storage Provider"}
            </CardTitle>
            <button
              type="button"
              onclick={closeDialog}
              class="cursor-pointer rounded-md p-1 text-muted hover:text-ink"
              aria-label="Close"
            >
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>
          <CardDescription>
            {editingId !== null
              ? "Update provider details and default configuration keys."
              : "Define a storage provider template and default configuration keys."}
          </CardDescription>
        </CardHeader>

        <CardContent class="overflow-y-auto">
          <form onsubmit={handleSubmit} novalidate class="space-y-4">
            <div class="grid gap-4 sm:grid-cols-2">
              <div class="grid gap-2">
                <Label for="provider-name">Name</Label>
                <Input
                  id="provider-name"
                  type="text"
                  placeholder="Cloud Storage"
                  bind:value={form.name}
                  oninput={() => {
                    if (editingId === null) {
                      form.slug = slugify(form.name);
                    }
                  }}
                  disabled={form.processing}
                  aria-invalid={Boolean(form.errors.name)}
                />
                {#if form.errors.name}
                  <p class="text-xs text-destructive">{form.errors.name}</p>
                {/if}
              </div>

              <div class="grid gap-2">
                <Label for="provider-slug">Slug</Label>
                <Input
                  id="provider-slug"
                  type="text"
                  placeholder="cloud-storage"
                  bind:value={form.slug}
                  disabled={form.processing}
                  aria-invalid={Boolean(form.errors.slug)}
                />
                {#if form.errors.slug}
                  <p class="text-xs text-destructive">{form.errors.slug}</p>
                {/if}
              </div>
            </div>

            <div class="border-t border-line pt-4">
              <div class="flex items-center justify-between gap-2">
                <Label>Provider Keys</Label>
                <Button type="button" variant="outline" size="sm" onclick={addKeyRow}>
                  Add key
                </Button>
              </div>

              {#if keyRows.length > 0}
                <div class="mt-3 space-y-2" transition:slide={{ duration: 200 }}>
                  {#each keyRows as row, index (index)}
                    <div class="grid grid-cols-[2fr_3fr_auto] gap-2" transition:slide={{ duration: 150 }}>
                      <Input type="text" placeholder="Key" bind:value={row.key} />
                      <Input type="text" placeholder="Value" bind:value={row.value} />
                      <Button
                        type="button"
                        variant="ghost"
                        size="icon"
                        onclick={() => removeKeyRow(index)}
                        aria-label="Remove key"
                        class="text-muted hover:bg-danger-soft hover:text-danger"
                      >
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-4">
                          <path d="M3 6h18"/>
                          <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                          <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                          <line x1="10" x2="10" y1="11" y2="17"/>
                          <line x1="14" x2="14" y1="11" y2="17"/>
                        </svg>
                      </Button>
                    </div>
                  {/each}
                </div>
              {/if}
            </div>

            <div class="flex items-center justify-end gap-2 pt-2">
              <Button type="button" variant="outline" onclick={closeDialog} disabled={form.processing}>
                Cancel
              </Button>
              <Button type="submit" disabled={form.processing}>
                {form.processing
                  ? "Saving…"
                  : editingId !== null
                    ? "Update provider"
                    : "Create provider"}
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </div>
{/if}

{#if menuProvider}
  <div
    bind:this={menuEl}
    role="menu"
    style={menuStyle}
    transition:fly={{ y: -2, duration: 100 }}
    class="fixed z-[60] w-40 rounded-md border border-line bg-page-elevated p-1 shadow-lg"
  >
    <button
      type="button"
      role="menuitem"
      onclick={handleMenuEdit}
      class="flex w-full cursor-pointer items-center gap-2 rounded-sm px-2.5 py-1.5 text-left text-sm text-ink hover:bg-surface-muted"
    >
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-3.5 text-muted">
        <path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/>
        <path d="m15 5 4 4"/>
      </svg>
      <span>Edit</span>
    </button>

    <button
      type="button"
      role="menuitem"
      disabled={deletingId === menuProvider.id}
      onclick={handleMenuDelete}
      class="flex w-full cursor-pointer items-center gap-2 rounded-sm px-2.5 py-1.5 text-left text-sm text-danger hover:bg-danger-soft disabled:cursor-not-allowed disabled:opacity-50"
    >
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-3.5">
        <path d="M3 6h18"/>
        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
      </svg>
      <span>{deletingId === menuProvider.id ? "Deleting…" : "Delete"}</span>
    </button>
  </div>
{/if}

{#if confirmDeleteProvider}
  <div
    role="dialog"
    aria-modal="true"
    aria-labelledby="provider-delete-dialog-title"
    tabindex="-1"
    class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4"
  >
    <button
      type="button"
      class="fixed inset-0 bg-black/50 backdrop-blur-xs"
      onclick={closeConfirmDelete}
      aria-label="Close modal"
      tabindex="-1"
    ></button>

    <div class="relative z-10 my-8 w-full max-w-md">
      <Card class="shadow-lg">
        <CardHeader>
          <CardTitle id="provider-delete-dialog-title">Delete Storage Provider</CardTitle>
          <CardDescription>
            Are you sure you want to delete <span class="font-medium text-ink">{confirmDeleteProvider.name}</span>? This action cannot be undone.
          </CardDescription>
        </CardHeader>

        <CardContent>
          <div class="flex items-center justify-end gap-2">
            <Button
              type="button"
              variant="outline"
              onclick={closeConfirmDelete}
              disabled={deletingId !== null}
            >
              Cancel
            </Button>
            <Button
              type="button"
              variant="destructive"
              onclick={confirmDelete}
              disabled={deletingId !== null}
            >
              {deletingId === confirmDeleteProvider.id ? "Deleting…" : "Delete"}
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
{/if}
