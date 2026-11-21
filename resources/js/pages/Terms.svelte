<script lang="ts">
  import LegalDocument from "@/components/LegalDocument.svelte";
  import { termsDocument } from "@/data/legal.js";
</script>

<svelte:head>
  <title>{termsDocument.title} - Nova Backups</title>
  <meta name="description" content={termsDocument.intro} />
</svelte:head>

<LegalDocument doc={termsDocument} />
