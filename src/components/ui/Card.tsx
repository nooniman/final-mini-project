/**
 * Card Component
 * A container component with shadow and rounded corners
 */

import React from 'react';
import { View, ViewProps, StyleSheet, TouchableOpacity } from 'react-native';
import { useTheme } from '../../context/ThemeContext';
import { Layout } from '../../constants/Layout';

interface CardProps extends ViewProps {
  variant?: 'default' | 'elevated' | 'outlined';
  padding?: 'none' | 'sm' | 'md' | 'lg';
  onPress?: () => void;
}

export function Card({
  children,
  variant = 'default',
  padding = 'md',
  onPress,
  style,
  ...props
}: CardProps) {
  const { colors, isDark } = useTheme();

  const paddingValue = {
    none: 0,
    sm: Layout.spacing.sm,
    md: Layout.spacing.md,
    lg: Layout.spacing.lg,
  }[padding];

  const getVariantStyles = () => {
    switch (variant) {
      case 'elevated':
        return {
          backgroundColor: colors.card,
          borderWidth: 0,
          ...(!isDark && Layout.shadow.md),
        };
      case 'outlined':
        return {
          backgroundColor: colors.card,
          borderWidth: 1,
          borderColor: colors.border,
        };
      default:
        return {
          backgroundColor: colors.card,
          borderWidth: 0,
          ...(!isDark && Layout.shadow.sm),
        };
    }
  };

  const cardStyles = [
    styles.card,
    getVariantStyles(),
    { padding: paddingValue },
    style,
  ];

  if (onPress) {
    return (
      <TouchableOpacity
        style={cardStyles}
        onPress={onPress}
        activeOpacity={0.7}
      >
        {children}
      </TouchableOpacity>
    );
  }

  return (
    <View style={cardStyles} {...props}>
      {children}
    </View>
  );
}

const styles = StyleSheet.create({
  card: {
    borderRadius: Layout.borderRadius.lg,
    overflow: 'hidden',
  },
});

export default Card;
