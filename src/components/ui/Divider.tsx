/**
 * Divider Component
 * Horizontal or vertical separator line
 */

import React from 'react';
import { View, StyleSheet } from 'react-native';
import { useTheme } from '../../context/ThemeContext';
import { Layout } from '../../constants/Layout';

interface DividerProps {
  orientation?: 'horizontal' | 'vertical';
  spacing?: 'none' | 'sm' | 'md' | 'lg';
  style?: object;
}

export function Divider({
  orientation = 'horizontal',
  spacing = 'md',
  style,
}: DividerProps) {
  const { colors } = useTheme();

  const spacingValue = {
    none: 0,
    sm: Layout.spacing.sm,
    md: Layout.spacing.md,
    lg: Layout.spacing.lg,
  }[spacing];

  const isHorizontal = orientation === 'horizontal';

  return (
    <View
      style={[
        isHorizontal ? styles.horizontal : styles.vertical,
        { backgroundColor: colors.divider },
        isHorizontal
          ? { marginVertical: spacingValue }
          : { marginHorizontal: spacingValue },
        style,
      ]}
    />
  );
}

const styles = StyleSheet.create({
  horizontal: {
    height: StyleSheet.hairlineWidth,
    width: '100%',
  },
  vertical: {
    width: StyleSheet.hairlineWidth,
    height: '100%',
  },
});

export default Divider;
