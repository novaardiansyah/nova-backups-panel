<script lang="ts">
  import GoogleIcon from "@/components/brand/google-icon.svelte";
  import { Button } from "@/components/ui/button/index.js";

  interface Props {
    label?: string;
    onclick?: (event: MouseEvent) => void;
  }

  let { label = "Continue with Google", onclick }: Props = $props();
</script>

<Button type="button" variant="outline" class="w-full" {onclick}>
  <GoogleIcon class="size-4" />
  <span>{label}</span>
</Button>
