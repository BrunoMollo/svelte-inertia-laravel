# Custom Claude Agents

This directory contains custom agent configurations for specialized tasks in this project.

## shadcn-installer

**Purpose:** Install shadcn-svelte UI components non-interactively

**Model:** Haiku (cost-effective for simple bash tasks)

**Tools:** Bash only

### Why This Agent?

Installing shadcn-svelte components requires handling interactive CLI prompts. This specialized agent:
- Uses the most cost-effective model (Haiku) for simple bash commands
- Has optimized instructions for non-interactive installation
- Reduces token usage by being focused on a single task

### How to Use

#### Option 1: Use the Helper Script (Recommended)
```bash
./scripts/install-shadcn-components.sh card dialog sheet
```

#### Option 2: Direct Command (for Claude agents)
```bash
echo y | pnpm dlx shadcn-svelte@latest add <components> --overwrite 2>&1
```

#### Option 3: Invoke via Claude Code
When Claude Code needs to install shadcn components, it can reference the strategy documented in this agent's configuration.

### What It Does

The agent handles:
1. **Interactive prompts**: Pipes `echo y` to confirm installation
2. **File conflicts**: Uses `--overwrite` flag to handle existing files
3. **Multiple components**: Can install several components in one command
4. **Error handling**: Captures both stdout and stderr for debugging

### Installation Pattern

```bash
# Single component
echo y | pnpm dlx shadcn-svelte@latest add card --overwrite

# Multiple components (more efficient)
echo y | pnpm dlx shadcn-svelte@latest add card dialog sheet table --overwrite

# With output capture
echo y | pnpm dlx shadcn-svelte@latest add card --overwrite 2>&1
```

## Creating New Agents

To create a new custom agent:

1. Create a YAML file in this directory: `your-agent-name.yaml`
2. Define the agent configuration:
   ```yaml
   name: your-agent-name
   description: What this agent does
   model: haiku|sonnet|opus
   tools:
     - ToolName1
     - ToolName2

   instructions: |
     Clear instructions for what the agent should do...
   ```
3. Document it in this README
4. Update CLAUDE.md if it's a commonly used agent

### Tips for Custom Agents

- **Use Haiku** for simple, repetitive tasks (cost-effective)
- **Use Sonnet** for complex reasoning or code generation
- **Use Opus** only when highest quality is critical
- **Minimize tools**: Only give access to what's needed
- **Clear instructions**: Be specific about the task and constraints
- **Error handling**: Include troubleshooting steps in instructions
