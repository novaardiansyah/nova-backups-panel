<script lang="ts">
  import { Button } from "@/components/ui/button/index.js";
  import { Card, CardContent, CardDescription, CardHeader, CardTitle } from "@/components/ui/card/index.js";

  interface Props {
    open: boolean;
    titleId: string;
    title: string;
    itemName: string;
    deleting?: boolean;
    onclose: () => void;
    onconfirm: () => void;
  }

  let {
    open,
    titleId,
    title,
    itemName,
    deleting = false,
    onclose,
    onconfirm,
  }: Props = $props();
</script>

<svelte:window
  onkeydown={(e) => {
    if (e.key === "Escape" && open) {
      onclose();
    }
  }}
/>

{#if open}
  <div
    role="dialog"
    aria-modal="true"
    aria-labelledby={titleId}
    tabindex="-1"
    class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto p-4"
  >
    <button
      type="button"
      class="fixed inset-0 bg-black/50 backdrop-blur-xs"
      onclick={onclose}
      aria-label="Close modal"
      tabindex="-1"
    ></button>

    <div class="relative z-10 my-8 w-full max-w-md">
      <Card class="shadow-lg">
        <CardHeader>
          <CardTitle id={titleId}>{title}</CardTitle>
          <CardDescription>
            Are you sure you want to delete <span class="font-medium text-ink">{itemName}</span>? This action cannot be undone.
          </CardDescription>
        </CardHeader>

        <CardContent>
          <div class="flex items-center justify-end gap-2">
            <Button
              type="button"
              variant="outline"
              onclick={onclose}
              disabled={deleting}
            >
              Cancel
            </Button>
            <Button
              type="button"
              variant="destructive"
              onclick={onconfirm}
              disabled={deleting}
            >
              {deleting ? "Deleting…" : "Delete"}
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
{/if}
