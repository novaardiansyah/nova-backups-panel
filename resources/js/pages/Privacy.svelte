<script lang="ts">
  import LegalDocument from "@/components/LegalDocument.svelte";
  import { privacyDocument } from "@/data/legal.js";
</script>

<svelte:head>
  <title>{privacyDocument.title} - Nova Backups</title>
  <meta name="description" content={privacyDocument.intro} />
</svelte:head>

<LegalDocument doc={privacyDocument} />
