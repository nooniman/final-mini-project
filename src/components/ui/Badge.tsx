/**
 * Badge Component
 * Small status indicator or counter
 */

import React from 'react';
import { View, StyleSheet } from 'react-native';
import { useTheme } from '../../context/ThemeContext';
import { Layout } from '../../constants/Layout';
import { ThemedText } from './ThemedText';

type BadgeVariant = 'primary' | 'secondary' | 'success' | 'warning' | 'error' | 'info';
type BadgeSize = 'sm' | 'md';

interface BadgeProps {
  label?: string;
  count?: number;
  variant?: BadgeVariant;
  size?: BadgeSize;
  dot?: boolean;
  style?: object;
}

export function Badge({
  label,
  count,
  variant = 'primary',
  size = 'md',
  dot = false,
  style,
}: BadgeProps) {
  const { colors } = useTheme();

  const variantColors: Record<BadgeVariant, { bg: string; text: string }> = {
    primary: { bg: colors.primary, text: '#FFFFFF' },
    secondary: { bg: colors.secondary, text: colors.text },
    success: { bg: colors.success, text: '#FFFFFF' },
    warning: { bg: colors.warning, text: colors.text },
    error: { bg: colors.error, text: '#FFFFFF' },
    info: { bg: colors.info, text: '#FFFFFF' },
  };

  const { bg, text } = variantColors[variant];

  if (dot) {
    return (
      <View
        style={[
          styles.dot,
          { backgroundColor: bg },
          size === 'sm' && styles.dotSm,
          style,
        ]}
      />
    );
  }

  const displayText = count !== undefined ? (count > 99 ? '99+' : String(count)) : label;

  return (
    <View
      style={[
        styles.badge,
        { backgroundColor: bg },
        size === 'sm' && styles.badgeSm,
        style,
      ]}
    >
      <ThemedText
        variant={size === 'sm' ? 'caption2' : 'caption1'}
        style={{ color: text }}
        weight="semibold"
      >
        {displayText}
      </ThemedText>
    </View>
  );
}

const styles = StyleSheet.create({
  badge: {
    paddingHorizontal: Layout.spacing.sm,
    paddingVertical: 2,
    borderRadius: Layout.borderRadius.full,
    alignSelf: 'flex-start',
  },
  badgeSm: {
    paddingHorizontal: 6,
    paddingVertical: 1,
  },
  dot: {
    width: 10,
    height: 10,
    borderRadius: 5,
  },
  dotSm: {
    width: 8,
    height: 8,
    borderRadius: 4,
  },
});

export default Badge;
