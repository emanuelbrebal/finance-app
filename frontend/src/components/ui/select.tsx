import * as React from "react";
import { cn } from "@/lib/utils";

// Lightweight native-select shim matching the shadcn/ui Select API.
// Replace with @radix-ui/react-select once the package is added.

interface SelectContextValue {
  value: string;
  onValueChange: (value: string) => void;
}

const SelectContext = React.createContext<SelectContextValue>({
  value: "",
  onValueChange: () => {},
});

interface SelectProps {
  value?: string;
  defaultValue?: string;
  onValueChange?: (value: string) => void;
  children: React.ReactNode;
}

function Select({ value, defaultValue = "", onValueChange, children }: SelectProps) {
  const [internal, setInternal] = React.useState(defaultValue);
  const controlled = value !== undefined;
  const current = controlled ? value : internal;

  const handleChange = (v: string) => {
    if (!controlled) setInternal(v);
    onValueChange?.(v);
  };

  return (
    <SelectContext.Provider value={{ value: current, onValueChange: handleChange }}>
      {children}
    </SelectContext.Provider>
  );
}

// SelectTrigger + SelectValue are rendered as a visible <select> element.
// SelectContent / SelectItem collect the options via a ref trick.

interface SelectTriggerProps extends React.HTMLAttributes<HTMLDivElement> {
  children?: React.ReactNode;
}

const SelectTrigger = React.forwardRef<HTMLSelectElement, SelectTriggerProps>(
  ({ className, children, ...props }, _ref) => {
    const { value, onValueChange } = React.useContext(SelectContext);
    // Extract SelectItem children from SelectContent buried in children
    const options = extractOptions(children);

    return (
      <select
        value={value}
        onChange={(e) => onValueChange(e.target.value)}
        className={cn(
          "flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background",
          "focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2",
          "disabled:cursor-not-allowed disabled:opacity-50",
          className
        )}
      >
        {options}
      </select>
    );
  }
);
SelectTrigger.displayName = "SelectTrigger";

// SelectValue just renders placeholder when nothing is selected
function SelectValue({ placeholder }: { placeholder?: string }) {
  const { value } = React.useContext(SelectContext);
  if (!value && placeholder) {
    return <option value="" disabled>{placeholder}</option>;
  }
  return null;
}

// SelectContent wraps SelectItems — its children are hoisted into the <select>
function SelectContent({ children }: { children: React.ReactNode }) {
  return <>{children}</>;
}

interface SelectItemProps extends React.OptionHTMLAttributes<HTMLOptionElement> {
  value: string;
  children: React.ReactNode;
}

function SelectItem({ value, children, className, ...props }: SelectItemProps) {
  return (
    <option value={value} className={cn("text-sm", className)} {...props}>
      {children}
    </option>
  );
}

// Utility: walk React children tree and collect <option> elements
function extractOptions(children: React.ReactNode): React.ReactNode {
  const options: React.ReactNode[] = [];

  React.Children.forEach(children, (child) => {
    if (!React.isValidElement(child)) return;
    const type = child.type as React.FC;

    if (type === SelectValue) {
      // render placeholder option
      const { placeholder } = child.props as { placeholder?: string };
      if (placeholder) {
        options.push(
          <option key="__placeholder" value="" disabled>
            {placeholder}
          </option>
        );
      }
    } else if (type === SelectContent) {
      options.push(...flattenItems((child.props as { children: React.ReactNode }).children));
    }
  });

  return options;
}

function flattenItems(children: React.ReactNode): React.ReactNode[] {
  const items: React.ReactNode[] = [];
  React.Children.forEach(children, (child) => {
    if (!React.isValidElement(child)) return;
    const type = child.type as React.FC;
    if (type === SelectItem) {
      const { value, children: label, ...rest } = child.props as SelectItemProps;
      items.push(
        <option key={value} value={value} {...rest}>
          {label}
        </option>
      );
    }
  });
  return items;
}

export { Select, SelectContent, SelectItem, SelectTrigger, SelectValue };
